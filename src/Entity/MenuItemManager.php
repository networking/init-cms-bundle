<?php

declare(strict_types=1);

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Query;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Networking\InitCmsBundle\Entity\BasePage;
use Networking\InitCmsBundle\Helper\PageHelper;
use Networking\InitCmsBundle\Model\MenuItemManagerInterface;
use Doctrine\ORM\Query\Expr;

/**
 * Class MenuItemManager.
 *
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
    protected $latestSnapshotIds = false;

    /**
     * MenuItemManager constructor.
     * @param EntityManagerInterface $om
     * @param PageHelper $pageHelper
     * @param $class
     */
    public function __construct(EntityManagerInterface $om, PageHelper $pageHelper , $class)
    {
        $this->pageHelper = $pageHelper;
        $classMetaData = $om->getClassMetadata($class);

        parent::__construct($om, $classMetaData);
    }

    public function setPageHelper(PageHelper $pageHelper)
    {
        $this->pageHelper = $pageHelper;
    }

    /**
     * @param $locale
     * @param null   $sortByField
     * @param string $direction
     *
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
     * @param null   $node
     * @param bool   $direct
     * @param null   $sortByField
     * @param string $direction
     * @param bool   $includeNode
     * @param string $viewStatus
     *
     * @return array
     */
    public function getChildrenByStatus(
        $node = null,
        $direct = false,
        $sortByField = null,
        $direction = 'ASC',
        $includeNode = false,
        $viewStatus = BasePage::STATUS_PUBLISHED
    ) {
        $qb = $this->childrenQueryBuilder($node, $direct, $sortByField, $direction, $includeNode);
        $aliases = $qb->getRootAliases();
        if ($viewStatus == BasePage::STATUS_PUBLISHED) {
            $orx = $qb->expr()->orX();
            $conditionA = $qb->expr()->in('ps.id', $this->getLatestSnapshotIds());
            $conditionB = $qb->expr()->isNull('ps.id');
            $orx->addMultiple([$conditionA, $conditionB]);
            $qb->addSelect('ps.id AS ps_id')
                ->addSelect('ps.versionedData AS ps_versionedData')
                ->leftJoin(PageSnapshot::class,
                    'ps',
                    Expr\Join::WITH,
                    sprintf('%s.page = ps.page ', $aliases[0])
                )
                ->andWhere($orx)
                ->leftJoin('ps.contentRoute', 'cr');
        } else {
            $qb->leftJoin(sprintf('%s.page', $aliases[0]), 'p')
                ->leftJoin('p.contentRoute', 'cr')
                ->addSelect('p');
        }
        $qb->addSelect('cr.path AS path');
        $results = $qb->getQuery()->getResult();

        $menuItems = [];
        foreach ($results as $item) {
            $menuItem = $item[0];
            if ($menuItem->getPage()) {
                if ($viewStatus == BasePage::STATUS_PUBLISHED) {
                    if (!$item['path'] || !$item['ps_id']) {
                        continue;
                    } else {
                        if (!$this->pageHelper->jsonPageIsActive($item['ps_versionedData'])) {
                            continue;
                        }
                        $menuItem->setPath($item['path']);
                    }
                } else {
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
     * Get all menu items and their pages if available.
     *
     * @return mixed
     */
    public function findAllJoinPage()
    {
        $qb = $this->createQueryBuilder('m');
        $qb->select('m,p');
        $qb->leftJoin('m.page', 'p');

        return $qb->getQuery()->execute();
    }

    /**
     * @return array
     */
    protected function getLatestSnapshotIds()
    {
        if (!$this->latestSnapshotIds) {
            $em = $this->getEntityManager();
            $metadata = $em->getClassMetadata(PageSnapshot::class);
            $rsm = new Query\ResultSetMapping();
            $rsm->addScalarResult('ps_id', 'ps_id');
            $qb = $em->createNativeQuery(sprintf('SELECT MAX(id) AS ps_id FROM %s GROUP BY page_id', $metadata->getTableName()), $rsm);
            $result = $qb->getScalarResult();
            $this->latestSnapshotIds = array_map('current', $result);
        }

        return $this->latestSnapshotIds;
    }
}
