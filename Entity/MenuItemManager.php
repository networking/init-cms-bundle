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
use Gedmo\Exception\InvalidArgumentException;
use Gedmo\Tool\Wrapper\EntityWrapper;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use JMS\Serializer\Serializer;
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
            if (!$menuItem->getRedirectUrl() && !$menuItem->getInternalUrl()) {
                if ($viewStatus == Page::STATUS_PUBLISHED) {
                    if (!$item['path'] || !$item['ps_id']) {
                        continue;
                    } else {
                        if (!$this->pageHelper->jsonPageIsActive($item['ps_versionedData'])) {
                            continue;
                        }
                        $menuItem->setPath($item['path']);
                    }
                }
            }
            $menuItems[] = $menuItem;
        }

        return $menuItems;
    }

    /**
     * @see getChildrenQueryBuilder
     */
    public function childrenQueryBuilder($node = null, $direct = false, $sortByField = null, $direction = 'ASC', $includeNode = false)
    {
        $meta = $this->getClassMetadata();
        $config = $this->listener->getConfiguration($this->_em, $meta->name);

        $qb = $this->getQueryBuilder();
        $qb->select('node, p')
            ->from($config['useObjectClass'], 'node')
            ->leftJoin('node.page', 'p')
        ;
        if ($node !== null) {
            if ($node instanceof $meta->name) {
                $wrapped = new EntityWrapper($node, $this->_em);
                if (!$wrapped->hasValidIdentifier()) {
                    throw new InvalidArgumentException("Node is not managed by UnitOfWork");
                }
                if ($direct) {
                    $id = $wrapped->getIdentifier();
                    $qb->where($id === null ?
                            $qb->expr()->isNull('node.'.$config['parent']) :
                            $qb->expr()->eq('node.'.$config['parent'], is_string($id) ? $qb->expr()->literal($id) : $id)
                    );
                } else {
                    $left = $wrapped->getPropertyValue($config['left']);
                    $right = $wrapped->getPropertyValue($config['right']);
                    if ($left && $right) {
                        $qb
                            ->where($qb->expr()->lt('node.' . $config['right'], $right))
                            ->andWhere($qb->expr()->gt('node.' . $config['left'], $left))
                        ;
                    }
                }
                if (isset($config['root'])) {
                    $rootId = $wrapped->getPropertyValue($config['root']);
                    $qb->andWhere($rootId === null ?
                            $qb->expr()->isNull('node.'.$config['root']) :
                            $qb->expr()->eq('node.'.$config['root'], is_string($rootId) ? $qb->expr()->literal($rootId) : $rootId)
                    );
                }
                if ($includeNode) {
                    $idField = $meta->getSingleIdentifierFieldName();
                    $qb->where('('.$qb->getDqlPart('where').') OR node.'.$idField.' = :rootNode');
                    $qb->setParameter('rootNode', $node);
                }
            } else {
                throw new \InvalidArgumentException("Node is not related to this repository");
            }
        } else {
            if ($direct) {
                $qb->where($qb->expr()->isNull('node.' . $config['parent']));
            }
        }
        if (!$sortByField) {
            $qb->orderBy('node.' . $config['left'], 'ASC');
        } elseif (is_array($sortByField)) {
            $fields = '';
            foreach ($sortByField as $field) {
                $fields .= 'node.'.$field.',';
            }
            $fields = rtrim($fields, ',');
            $qb->orderBy($fields, $direction);
        } else {
            if ($meta->hasField($sortByField) && in_array(strtolower($direction), array('asc', 'desc'))) {
                $qb->orderBy('node.' . $sortByField, $direction);
            } else {
                throw new InvalidArgumentException("Invalid sort options specified: field - {$sortByField}, direction - {$direction}");
            }
        }

        return $qb;
    }


} 