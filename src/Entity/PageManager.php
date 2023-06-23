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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\TransactionRequiredException;
use Gedmo\Tool\Wrapper\EntityWrapper;
use Gedmo\Tree\Entity\Repository\AbstractTreeRepository;
use Gedmo\Tree\Entity\Repository\MaterializedPathRepository;
use Gedmo\Tree\Strategy;
use JMS\Serializer\SerializerInterface;
use Networking\InitCmsBundle\Model\IgnoreRevertInterface;
use Networking\InitCmsBundle\Model\PageInterface;
use Networking\InitCmsBundle\Model\PageManagerInterface;
use Networking\InitCmsBundle\Serializer\PageSnapshotDeserializationContext;
use ReflectionClass;
use ReflectionException;

/**
 * Class PageManager.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class PageManager extends AbstractTreeRepository implements PageManagerInterface
{
    /**
     * PageManager constructor.
     * @param EntityManagerInterface $om
     * @param $class
     */
    public function __construct(EntityManagerInterface $om, $class)
    {
        if (class_exists($class)) {
            $classMetaData = $om->getClassMetadata($class);

            parent::__construct($om, $classMetaData);
        }
    }

    /**
     * @param string $id
     *
     * @return object
     */
    public function findById($id): ?object
    {
        return $this->find($id);
    }

    /**
     * @param $locale
     * @param null $id
     * @param bool $showHome
     * @param bool $showChildren
     *
     * @return \Doctrine\ORM\QueryBuilder|mixed
     */
    public function getParentPagesQuery($locale, $id = null, $showHome = false, $showChildren = false): mixed
    {
        $qb = $this->createQueryBuilder('p');
        if (!$showHome) {
            $qb->where($qb->expr()->isNull('p.isHome').' OR p.isHome <> 1');
        }
        if ($id) {
            if (!$showChildren) {
                /** @var $page PageInterface */
                $page = $this->find($id);
                $collection = new ArrayCollection($page->getAllChildren());
                $childrenIds = $collection->map(
                    fn(PageInterface $p) => $p->getId()
                );

                if ($childrenIds->count()) {
                    $qb->andWhere($qb->expr()->notIn('p.id', $childrenIds->toArray()));
                }
            }
            $qb->andWhere($qb->expr()->neq('p.id', $id));
        }

        $qb->andWhere('p.locale = :locale');
        $qb->orderBy('p.path', 'ASC');

        $qb->setParameter(':locale', $locale);

        return $qb;
    }

    /**
     * @param $locale
     * @param null $id
     */
    public function getParentPagesChoices($locale, $id = null): mixed
    {
        $qb = $this->getParentPagesQuery($locale, $id);

        return $qb->getQuery()->execute();
    }

    /**
     * @param $sort
     * @param string $order
     * @param int $hydrationMode
     */
    public function getAllSortBy($sort, $order = 'DESC', $hydrationMode = Query::HYDRATE_OBJECT): mixed
    {
        $query = $this->getAllSortByQuery($sort, $order);

        return $query->execute([], $hydrationMode);
    }

    /**
     * @param $sort
     * @param string $order
     */
    public function getAllSortByQuery($sort, $order = 'DESC'): Query
    {
        $qb = $this->createQueryBuilder('p');
        $qb2 = $this->getEntityManager()->getRepository(PageSnapshot::class)->createQueryBuilder('pp');
        $qb->select('p', 'ps')
            ->leftJoin('p.snapshots', 'ps')
            ->where(
                $qb->expr()->eq(
                    'ps.id',
                    '('.$qb2->select('MAX(pp.id)')
                        ->where('p.id = pp.page')
                        ->getDQL().')'
                )
            )
            ->orWhere('ps.id IS NULL')
            ->orderBy('p.'.$sort, $order);

        return $qb->getQuery();
    }

    /**
     * Return the content identifier for the provided content object for
     * debugging purposes.
     *
     * @param object $content A content instance
     *
     * @return string|null $id id of the content object or null if unable to determine an id
     */
    public function getContentId($content): ?string
    {
        return $content->getId();
    }

    /**
     * @param \JMS\Serializer\Serializer $serializer
     *
     */
    public function revertToPublished(PageInterface $draftPage, SerializerInterface $serializer): PageInterface
    {
        $currentLayoutBlocks = $draftPage->getLayoutBlock();
        $pageSnapshot = $draftPage->getSnapshot();
        $contentRoute = $draftPage->getContentRoute();

        $context = new PageSnapshotDeserializationContext();
        $context->setDeserializeTranslations(true);

        /** @var $publishedPage PageInterface */
        $publishedPage = $serializer->deserialize(
            $pageSnapshot->getVersionedData(),
            $this->getClassName(),
            'json',
            $context
        );

        $contentRoute->setTemplate($pageSnapshot->getContentRoute()->getTemplate());
        $contentRoute->setTemplateName($pageSnapshot->getContentRoute()->getTemplateName());
        $contentRoute->setController($pageSnapshot->getContentRoute()->getController());
        $contentRoute->setPath($pageSnapshot->getContentRoute()->getPath());

        $draftPage->restoreFromPublished($publishedPage);


        $layoutBlockIds = [];

        // Set the layout blocks of the NOW managed entity to
        // exactly that of the published version
        foreach ($publishedPage->getLayoutBlock() as $publishedlayoutBlock) {


            $matches = $currentLayoutBlocks->filter(
                fn(LayoutBlock $layoutBlock) => $publishedlayoutBlock->getId() === $layoutBlock->getId()
            );
            $layoutBlock = $matches->first();

            if (!$layoutBlock) {
                $layoutBlock = new LayoutBlock();
                $layoutBlock->setPage($draftPage);
                $currentLayoutBlocks->add($layoutBlock);
            }

            $layoutBlock->restoreFormPublished($publishedlayoutBlock);

            $layoutBlock = $this->resetContent($layoutBlock, $serializer);

            $layoutBlockIds[] = $layoutBlock->getId() ?: null;
        }

        $currentLayoutBlocks = $this->cleanLayoutBlocks($currentLayoutBlocks, $layoutBlockIds);
        $draftPage->setLayoutBlock($currentLayoutBlocks);
        $this->_em->persist($contentRoute);
        $this->_em->persist($draftPage);
        $this->_em->flush();

        return $draftPage;
    }

    /**
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws ReflectionException
     */
    public function resetContent(LayoutBlock $layoutBlock, SerializerInterface $serializer): LayoutBlock
    {

        if ($snapshotContent = $layoutBlock->getSnapshotContent()) {

            $publishedContent = $serializer->deserialize($snapshotContent, $layoutBlock->getClassType(), 'json');

            $reflection = new ReflectionClass($publishedContent);
            foreach ($reflection->getProperties() as $property) {
                $this->revertObject($publishedContent, $property);
            }

            $this->_em->persist($publishedContent);
            $this->_em->flush();

            $layoutBlock->setObjectId($publishedContent->getId());

        }

        return $layoutBlock;
    }

    /**
     * @param $object
     * @param $var
     * @param $property
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     * @throws ReflectionException
     */
    public function revertObject($object, $property): mixed
    {
        $reflection = new ReflectionClass($object);

        if ($reflection->implementsInterface(IgnoreRevertInterface::class)) {
            dump($object);
            return $object;
        }

        $method = sprintf('get%s', ucfirst((string) $property->getName()));

        if (!$reflection->hasMethod($method)) {
            return $object;
        }

        $var = $object->{$method}();

        if ($var instanceof Collection) {

            if ($var->count() < 1) {
                return $object;
            }

            $newCollection = new ArrayCollection();

            foreach ($var as $v) {
                $newVar = $this->revertObjectVars($object, $v);
                $newCollection->add($newVar);
            }

            $var->clear();
            $method = sprintf('set%s', ucfirst((string) $property->getName()));
            $reflection = new ReflectionClass($object);
            if ($reflection->hasMethod($method)) {
                $object->{$method}($newCollection);
            }

            return $object;

        }

        if (is_object($var) && $this->_em->getMetadataFactory()->hasMetadataFor($var::class)) {

            $newVar = $this->revertObjectVars($object, $var);
            $this->_em->persist($newVar);

            $method = sprintf('set%s', ucfirst((string) $property->getName()));
            if ($reflection->hasMethod($method)) {
                $object->{$method}($newVar);
            }

            return $object;

        }

        $method = sprintf('set%s', ucfirst((string) $property->getName()));
        if ($reflection->hasMethod($method)) {
            $object->{$method}($var);
        }

        return $object;
    }

    /**
     * @param $parent
     * @param $oldObject
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     * @throws ReflectionException
     */
    public function revertObjectVars($parent, $oldObject): ?object
    {
        $reflection = new ReflectionClass($oldObject);

        if ($reflection->implementsInterface(IgnoreRevertInterface::class)) {
            return $oldObject;
        }
        $object = clone $oldObject;

        foreach ($reflection->getProperties() as $reflectionProperty) {
            $method = sprintf('get%s', ucfirst($reflectionProperty->getName()));
            if ($reflection->hasMethod($method)) {
                $val = $object->{$method}();
                /** Prevent Recursive loop */
                if ($val !== $parent) {
                    $this->revertObject($object, $reflectionProperty);
                }
            }
        }

        return $object;
    }

    public function cleanLayoutBlocks(Collection $currentLayoutBlocks, array $layoutBlockIds): Collection
    {

        $blocksToRemove = $currentLayoutBlocks->filter(
            fn(LayoutBlock $oldBlock) => !in_array($oldBlock->getId(), $layoutBlockIds)
        );

        foreach ($blocksToRemove as $deadBlock) {
            $currentLayoutBlocks->removeElement($deadBlock);
        }

        return $currentLayoutBlocks;

    }

    public function save(PageInterface $page): mixed
    {
        if (!$page->getId() && !$page->getContentRoute()->getTemplateName()) {
            $page->setContentRoute(new ContentRoute());
        }
        $this->_em->persist($page);

        return true;
    }

    public function resetEntityManager($em)
    {
        $this->_em = $em;
    }

    /**
     * Get tree query builder
     *
     * @param object $rootNode
     */
    public function getTreeQueryBuilder($rootNode = null): QueryBuilder
    {
        return $this->getChildrenQueryBuilder($rootNode, false, null, 'asc', true);
    }

    /**
     * Get tree query
     *
     * @param object $rootNode
     */
    public function getTreeQuery($rootNode = null): Query
    {
        return $this->getTreeQueryBuilder($rootNode)->getQuery();
    }

    /**
     * Get tree
     *
     * @param object $rootNode
     *
     * @return array
     */
    public function getTree($rootNode = null): mixed
    {
        return $this->getTreeQuery($rootNode)->execute();
    }

    public function getRootNodesQueryBuilder($sortByField = null, $direction = 'asc'): QueryBuilder
    {
        return $this->getChildrenQueryBuilder(null, true, $sortByField, $direction);
    }

    public function getRootNodesQuery($sortByField = null, $direction = 'asc'): Query
    {
        return $this->getRootNodesQueryBuilder($sortByField, $direction)->getQuery();
    }

    public function getRootNodes($sortByField = null, $direction = 'asc'): array
    {
        return $this->getRootNodesQuery($sortByField, $direction)->execute();
    }

    /**
     * Get the Tree path query builder by given $node
     *
     * @param object $node
     */
    public function getPathQueryBuilder($node): QueryBuilder
    {
        $meta = $this->getClassMetadata();
        $config = $this->listener->getConfiguration($this->_em, $meta->getName());
        $alias = 'materialized_path_entity';
        $qb = $this->getQueryBuilder()
            ->select($alias)
            ->from($config['useObjectClass'], $alias);

        $node = new EntityWrapper($node, $this->_em);
        $nodePath = $node->getPropertyValue($config['path']);
        $paths = [];
        $nodePathLength = strlen((string) $nodePath);
        $separatorMatchOffset = 0;
        while ($separatorMatchOffset < $nodePathLength) {
            $separatorPos = strpos((string) $nodePath, $config['path_separator'], $separatorMatchOffset);

            if (false === $separatorPos || $separatorPos === $nodePathLength - 1) {
                // last node, done
                $paths[] = $nodePath;
                $separatorMatchOffset = $nodePathLength;
            } elseif (0 === $separatorPos) {
                // path starts with separator, continue
                $separatorMatchOffset = 1;
            } else {
                // add node
                $paths[] = substr((string) $nodePath, 0, $config['path_ends_with_separator'] ? $separatorPos + 1 : $separatorPos);
                $separatorMatchOffset = $separatorPos + 1;
            }
        }
        $qb->where($qb->expr()->in(
            $alias.'.'.$config['path'],
            $paths
        ));
        $qb->orderBy($alias.'.'.$config['level'], 'ASC');

        return $qb;
    }

    /**
     * Get the Tree path query by given $node
     *
     * @param object $node
     */
    public function getPathQuery($node): Query
    {
        return $this->getPathQueryBuilder($node)->getQuery();
    }

    /**
     * Get the Tree path of Nodes by given $node
     *
     * @param object $node
     *
     * @return array list of Nodes in path
     */
    public function getPath($node): mixed
    {
        return $this->getPathQuery($node)->getResult();
    }

    public function getChildrenQueryBuilder($node = null, $direct = false, $sortByField = null, $direction = 'asc', $includeNode = false): QueryBuilder
    {
        $meta = $this->getClassMetadata();
        $config = $this->listener->getConfiguration($this->_em, $meta->getName());
        $separator = addcslashes($config['path_separator'], '%');
        $alias = 'materialized_path_entity';
        $path = $config['path'];
        $qb = $this->getQueryBuilder()
            ->select($alias)
            ->from($config['useObjectClass'], $alias);
        $expr = '';
        $includeNodeExpr = '';

        if (is_a($node, $meta->getName())) {
            $node = new EntityWrapper($node, $this->_em);
            $nodePath = $node->getPropertyValue($path);
            $expr = $qb->expr()->andx()->add(
                $qb->expr()->like(
                    $alias.'.'.$path,
                    $qb->expr()->literal(
                        $nodePath
                        .($config['path_ends_with_separator'] ? '' : $separator).'%'
                    )
                )
            );

            if ($includeNode) {
                $includeNodeExpr = $qb->expr()->eq($alias.'.'.$path, $qb->expr()->literal($nodePath));
            } else {
                $expr->add($qb->expr()->neq($alias.'.'.$path, $qb->expr()->literal($nodePath)));
            }

            if ($direct) {
                $expr->add(
                    $qb->expr()->orx(
                        $qb->expr()->eq($alias.'.'.$config['level'], $qb->expr()->literal($node->getPropertyValue($config['level']))),
                        $qb->expr()->eq($alias.'.'.$config['level'], $qb->expr()->literal($node->getPropertyValue($config['level']) + 1))
                    )
                );
            }
        } elseif ($direct) {
            $expr = $qb->expr()->not(
                $qb->expr()->like($alias.'.'.$path,
                    $qb->expr()->literal(
                        ($config['path_starts_with_separator'] ? $separator : '')
                        .'%'.$separator.'%'
                        .($config['path_ends_with_separator'] ? $separator : '')
                    )
                )
            );
        }

        if ($expr) {
            $qb->where('('.$expr.')');
        }

        if ($includeNodeExpr) {
            $qb->orWhere('('.$includeNodeExpr.')');
        }

        $orderByField = null === $sortByField ? $alias.'.'.$config['path'] : $alias.'.'.$sortByField;
        $orderByDir = 'asc' === $direction ? 'asc' : 'desc';
        $qb->orderBy($orderByField, $orderByDir);

        return $qb;
    }

    public function getChildrenQuery($node = null, $direct = false, $sortByField = null, $direction = 'asc', $includeNode = false): Query
    {
        return $this->getChildrenQueryBuilder($node, $direct, $sortByField, $direction, $includeNode)->getQuery();
    }

    public function getChildren($node = null, $direct = false, $sortByField = null, $direction = 'asc', $includeNode = false): ?array
    {
        return $this->getChildrenQuery($node, $direct, $sortByField, $direction, $includeNode)->execute();
    }

    public function getNodesHierarchyQueryBuilder($node = null, $direct = false, array $options = [], $includeNode = false): QueryBuilder
    {
        $sortBy = [
            'field' => null,
            'dir' => 'asc',
        ];

        if (isset($options['childSort'])) {
            $sortBy = array_merge($sortBy, $options['childSort']);
        }

        return $this->getChildrenQueryBuilder($node, $direct, $sortBy['field'], $sortBy['dir'], $includeNode);
    }

    public function getNodesHierarchyQuery($node = null, $direct = false, array $options = [], $includeNode = false): Query
    {
        return $this->getNodesHierarchyQueryBuilder($node, $direct, $options, $includeNode)->getQuery();
    }

    public function getNodesHierarchy($node = null, $direct = false, array $options = [], $includeNode = false): array
    {
        $meta = $this->getClassMetadata();
        $config = $this->listener->getConfiguration($this->_em, $meta->getName());
        $path = $config['path'];

        $nodes = $this->getNodesHierarchyQuery($node, $direct, $options, $includeNode)->getArrayResult();
        usort(
            $nodes,
            static fn($a, $b): int => strcmp((string) $a[$path], (string) $b[$path])
        );

        return $nodes;
    }

    protected function validate(): bool
    {
        return Strategy::MATERIALIZED_PATH === $this->listener->getStrategy($this->_em, $this->getClassMetadata()->name)->getName();
    }
}
