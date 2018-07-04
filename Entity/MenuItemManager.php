<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Entity;

use Doctrine\ORM\Query;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Networking\InitCmsBundle\Helper\PageHelper;
use Networking\InitCmsBundle\Model\MenuItemManagerInterface;
use Doctrine\ORM\EntityManager;
use Networking\InitCmsBundle\Model\Page;
use Doctrine\ORM\Query\Expr;

/**
 * Class MenuItemManager
 * @package Networking\InitCmsBundle\Entity
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class MenuItemManager extends NestedTreeRepository implements MenuItemManagerInterface
{


    /**
     * @var PageHelper
     */
    protected $pageHelper;

    /**
     * @var bool
     */
    protected $lastestSnapshotIds = false;

    /**
     * @param EntityManager $em
     * @param \Doctrine\ORM\Mapping\ClassMetadata $class
     */
    public function __construct(EntityManager $em, $class)
    {
        $classMetaData = $em->getClassMetadata($class);

        parent::__construct($em, $classMetaData);
    }

    public function setPageHelper(PageHelper $pageHelper)
    {
        $this->pageHelper = $pageHelper;
    }

    /**
     * @param $locale
     * @param  null $sortByField
     * @param  string $direction
     * @return array
     */
    public function getRootNodesByLocale($locale, $sortByField = null, $direction = 'asc')
    {
        $rootNodesQueryBuilder = $this->getRootNodesQueryBuilder($sortByField, $direction);
        $alias = $rootNodesQueryBuilder->getRootAliases();
        $rootNodesQueryBuilder->andWhere(sprintf('%s.locale = :locale', $alias[0]))
            ->setParameter(':locale', $locale);

        return $rootNodesQueryBuilder->getQuery()->getResult();
    }

    /**
     * @param null $node
     * @param bool $direct
     * @param null $sortByField
     * @param string $direction
     * @param bool $includeNode
     * @param string $viewStatus
     * @return array
     */
    public function getChildrenByStatus(
        $node = null,
        $direct = false,
        $sortByField = null,
        $direction = 'ASC',
        $includeNode = false,
        $viewStatus = Page::STATUS_PUBLISHED
    ) {

        $qb = $this->childrenQueryBuilder($node, $direct, $sortByField, $direction, $includeNode);
        $aliases = $qb->getRootAliases();




        if ($viewStatus == Page::STATUS_PUBLISHED) {
            $qb->addSelect('ps.id AS ps_id');
            $qb->addSelect('ps.versionedData AS ps_versionedData');
            $qb->leftJoin(
                '\Networking\InitCmsBundle\Entity\PageSnapshot',
                'ps',
                Expr\Join::WITH,
                sprintf('%s.page = ps.page ', $aliases[0])
            );
            $qb->andWhere($qb->expr()->in('ps.id', $this->getLastestSnapshotIds()));
            $qb->leftJoin('ps.contentRoute', 'cr');

        } else {
            $qb->leftJoin(sprintf('%s.page', $aliases[0]), 'p');
            $qb->leftJoin('p.contentRoute', 'cr');
        }
        $qb->addSelect('cr.path AS path');
        $results = $qb->getQuery()->getResult();

        $menuItems = array();
        foreach ($results as $item) {
            $menuItem = $item[0];
            if ($menuItem->getPage()) {
                if ($viewStatus == Page::STATUS_PUBLISHED) {
                    if (!$item['path'] || !$item['ps_id']) {
                        continue;
                    } else {
                        if (!$this->pageHelper->jsonPageIsActive($item['ps_versionedData'])) {
                            continue;
                        }
                        $menuItem->setPath($item['path']);
                    }
                }else{
                    if (!$item['path']) {
                        continue;
                    } else {
                        $menuItem->setPath($item['path']);
                    }
                }
            }
            $menuItems[] = $menuItem;
        }

        return $menuItems;
    }


    /**
     * Get all menu items and their pages if available
     *
     * @return mixed
     */
    public function findAllJoinPage()
    {
        $qb =$this->createQueryBuilder('m');
        $qb->select('m,p');
        $qb->leftJoin('m.page', 'p');

        return $qb->getQuery()->execute();
    }

    /**
     * @return array
     */
    protected function getLastestSnapshotIds()
    {
        if(!$this->lastestSnapshotIds){
            $em = $this->getEntityManager();
            $metadata = $em->getClassMetadata('\Networking\InitCmsBundle\Entity\PageSnapshot');
            $rsm = new Query\ResultSetMapping();
            $rsm->addScalarResult('ps_id', 'ps_id');
            $qb = $em->createNativeQuery(sprintf("SELECT MAX(id) AS ps_id FROM %s GROUP BY page_id", $metadata->getTableName()), $rsm);
            $result = $qb->getScalarResult();
            $this->lastestSnapshotIds = array_map('current', $result);
        }
        return $this->lastestSnapshotIds;
    }


} 