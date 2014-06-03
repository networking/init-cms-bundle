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

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use JMS\Serializer\Serializer;
use Networking\InitCmsBundle\Helper\PageHelper;
use Networking\InitCmsBundle\Model\MenuItemManagerInterface;
use Doctrine\ORM\EntityManager;
use Networking\InitCmsBundle\Model\Page;

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
     * @param EntityManager $em
     * @param \Doctrine\ORM\Mapping\ClassMetadata $class
     */
    public function __construct(EntityManager $em, $class)
    {
        $classMetaData = $em->getClassMetadata($class);

        parent::__construct($em, $classMetaData);
    }

    public function setPageHelper(PageHelper $pageHelper){
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
        $qb->leftJoin(sprintf('%s.page', $aliases[0]), 'p');
        $qb->addSelect('cr.path AS path');

        if ($viewStatus == Page::STATUS_PUBLISHED) {
            $qb->leftJoin('p.snapshots', 'ps');
            $qb->leftJoin('ps.contentRoute', 'cr');
            $qb->andWhere($qb->expr()->orX('p.id = ps.page', 'p.id IS NULL'));

        } else {
            $qb->leftJoin('p.contentRoute', 'cr');
        }

        $results = $qb->getQuery()->getResult();

        $menuItems = array();
        foreach ($results as $item) {
            $menuItem = $item[0];
            if ($viewStatus == Page::STATUS_PUBLISHED) {
                if($page = $menuItem->getPage()){
                    if(!$page->getSnapshot()){
                        continue;
                    }
                    $snapshot = $page->getSnapshot();
                    $page = $this->pageHelper->unserializePageSnapshotData($snapshot);
                    if(!$page->isActive()){
                        continue;
                    }

                }
            }

            $menuItem->setPath($item['path']);
            $menuItems[] = $menuItem;
        }


        return $menuItems;
    }

} 