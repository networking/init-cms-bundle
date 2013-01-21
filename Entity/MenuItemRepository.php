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

/**
 *  @author net working AG <info@networking.ch>
 */
class MenuItemRepository extends NestedTreeRepository
{
    /**
     * {@inheritDoc}
     */
    public function getNodesHierarchyQuery($node = null, $direct = false, array $options = array(), $includeNode = false)
    {
        return $this->getNodesHierarchyQueryBuilder($node, $direct, $options, $includeNode)->getQuery();
    }

    /**
     * {@inheritdoc}
     */
    public function getNodesHierarchy($node = null, $direct = false, array $options = array(), $includeNode = false)
    {
        return $this->getNodesHierarchyQuery($node, $direct, $options, $includeNode)->getArrayResult();
    }

    /**
     * @param $locale
     * @param  null   $sortByField
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
}
