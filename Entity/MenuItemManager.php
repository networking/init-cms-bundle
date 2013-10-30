<?php
/**
 * Created by PhpStorm.
 * User: marcbissegger
 * Date: 10/24/13
 * Time: 3:08 PM
 */

namespace Networking\InitCmsBundle\Entity;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Networking\InitCmsBundle\Model\MenuItemManagerInterface;
use Doctrine\ORM\EntityManager;


class MenuItemManager extends NestedTreeRepository implements MenuItemManagerInterface{

    public function __construct(EntityManager $em, $class)
    {
        $classMetaData = $em->getClassMetadata($class);

        parent::__construct($em, $classMetaData);
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

    public function getChildrenByStatus($node = null, $direct = false, $sortByField = null, $direction = 'ASC', $includeNode = false, $viewStatus = BasePage::STATUS_PUBLISHED)
    {

        $qb = $this->childrenQueryBuilder($node, $direct, $sortByField, $direction, $includeNode);
        $aliases = $qb->getRootAliases();
        $qb->leftJoin(sprintf('%s.page', $aliases[0]), 'p');
        $qb->addSelect('cr.path AS path');

        if ($viewStatus == BasePage::STATUS_PUBLISHED) {
            $qb->leftJoin('p.snapshots', 'ps');
            $qb->leftJoin('ps.contentRoute', 'cr');
            $qb->andWhere($qb->expr()->orX('p.id = ps.page', 'p.id IS NULL'));
        }else{
            $qb->leftJoin('p.contentRoute', 'cr');
        }

        $results = $qb->getQuery()->getResult();
        $menuItems = array();
        foreach($results as $item){
            $menuItem = $item[0];
            $menuItem->setPath($item['path']);
            $menuItems[] = $menuItem;
        }


        return $menuItems;
    }

} 