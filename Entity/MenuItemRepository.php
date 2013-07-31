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

use Doctrine\ORM\Query\Expr\Join;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Symfony\Component\Config\Definition\Builder\ExprBuilder;

/**
 * @author net working AG <info@networking.ch>
 */
class MenuItemRepository extends NestedTreeRepository
{

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

    public function getChildrenByStatus($node = null, $direct = false, $sortByField = null, $direction = 'ASC', $includeNode = false, $viewStatus = Page::STATUS_PUBLISHED)
    {
        $qb = $this->childrenQueryBuilder($node, $direct, $sortByField, $direction, $includeNode);
        $aliases = $qb->getRootAliases();
        $qb->leftJoin(sprintf('%s.page', $aliases[0]), 'p');
        $qb->addSelect('cr.path AS path');
        if ($viewStatus == Page::STATUS_PUBLISHED) {
            $qb->leftJoin('p.snapshots', 'ps');
            $qb->leftJoin('ps.contentRoute', 'cr');
            $qb->andWhere($qb->expr()->orX('p.id = ps.page', 'p.id IS NULL'));
        }else{
            $qb->leftJoin('p.contentRoute', 'cr');
        }
        $qb->groupBy('p.id');

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
