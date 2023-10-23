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
use Doctrine\ORM\PersistentCollection;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Doctrine\ORM\TransactionRequiredException;
use Gedmo\Tool\Wrapper\EntityWrapper;
use Gedmo\Tree\Entity\Repository\AbstractTreeRepository;
use Gedmo\Tree\Strategy;
use Networking\InitCmsBundle\Helper\PageHelper;
use Networking\InitCmsBundle\Model\IgnoreRevertInterface;
use Networking\InitCmsBundle\Model\PageInterface;
use Networking\InitCmsBundle\Model\PageManagerInterface;
use Networking\InitCmsBundle\Model\PageSnapshotInterface;
use ReflectionClass;
use ReflectionException;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;

/**
 * Class PageManager.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class PageManager extends AbstractTreeRepository implements PageManagerInterface
{
    public $mergingInfos = [];

    public $deserializedEntities = [];


    /**
     * PageManager constructor.
     *
     * @param EntityManagerInterface $om
     * @param                        $class
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
        return $this->findOneBy(['id' => $id]);
    }

    /**
     * @param      $locale
     * @param null $id
     * @param bool $showHome
     * @param bool $showChildren
     *
     * @return \Doctrine\ORM\QueryBuilder|mixed
     */
    public function getParentPagesQuery(
        $locale,
        $id = null,
        $showHome = false,
        $showChildren = false
    ): mixed {
        $qb = $this->createQueryBuilder('p');
        if (!$showHome) {
            $qb->where($qb->expr()->isNull('p.isHome').' OR p.isHome <> 1');
        }
        if ($id) {
            if (!$showChildren) {
                /** @var $page PageInterface */
                $page = $this->findById($id);
                $collection = new ArrayCollection($page->getAllChildren());
                $childrenIds = $collection->map(
                    fn(PageInterface $p) => $p->getId()
                );

                if ($childrenIds->count()) {
                    $qb->andWhere(
                        $qb->expr()->notIn('p.id', $childrenIds->toArray())
                    );
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
     * @param      $locale
     * @param null $id
     */
    public function getParentPagesChoices($locale, $id = null): mixed
    {
        $qb = $this->getParentPagesQuery($locale, $id);

        return $qb->getQuery()->execute();
    }

    /**
     * @param        $sort
     * @param string $order
     * @param int    $hydrationMode
     */
    public function getAllSortBy(
        $sort,
        $order = 'DESC',
        $hydrationMode = Query::HYDRATE_OBJECT
    ): mixed {
        $query = $this->getAllSortByQuery($sort, $order);

        return $query->execute([], $hydrationMode);
    }

    /**
     * @param        $sort
     * @param string $order
     */
    public function getAllSortByQuery($sort, $order = 'DESC'): Query
    {
        $qb = $this->createQueryBuilder('p');
        $qb2 = $this->_em->getRepository(PageSnapshot::class)
            ->createQueryBuilder('pp');
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

    public function revertToPublished(PageInterface $page, $serializer){
        return $this->cancelDraft($page, $serializer);
    }

    public function cancelDraft(PageInterface $page, \Symfony\Component\Serializer\SerializerInterface $serializer)
    {
        $currentLayoutBlocks = $page->getLayoutBlocks();
        $pageSnapshot = $page->getSnapshot();
        $contentRoute = $page->getContentRoute();
        $pageManager = $this;
        $context = [
            AbstractObjectNormalizer::DEEP_OBJECT_TO_POPULATE => true,
            AbstractObjectNormalizer::OBJECT_TO_POPULATE => $page,
            AbstractObjectNormalizer::SKIP_UNINITIALIZED_VALUES => true,
            AbstractNormalizer::CALLBACKS => [
                'translations' => function ( $innerObject, $outerObject, string $attributeName, string $format = null, array $context = []) use($pageManager): array {
                    $translations = [];
                    foreach ($innerObject as $key => $page){
                        $translations[$key] = $pageManager->findById(
                            $page->getId()
                        );
                        $page->setTranslations($translations);
                    }
                    return  $translations ;
                },
                'original' => function ( $innerObject, $outerObject, string $attributeName, string $format = null, array $context = []) use($pageManager): ?PageInterface {
                    return $innerObject ? $pageManager->findById(
                        $innerObject
                    ):null;
                },
                'alias' => function ( $innerObject, $outerObject, string $attributeName, string $format = null, array $context = []) use($pageManager): ?PageInterface {
                    return $innerObject ? $pageManager->findById(
                        $innerObject
                    ):null;
                },

            ],
            AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT => true,
        ];
        $page = $serializer->deserialize($pageSnapshot->getVersionedData(), $page::class, 'json', $context);


        foreach ($page->getLayoutBlocks() as $layoutBlock) {

            if(!$layoutBlock->getId()){
                $this->resetContent($layoutBlock);
            }
            $this->_em->persist($layoutBlock);

        }


        $this->_em->persist($page);

        $this->_em->flush();
//
        return $page;
    }




    /**
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws ReflectionException
     */
    public function resetContent(LayoutBlock $layoutBlock): LayoutBlock
    {
        $reflection = new ReflectionClass($layoutBlock);

        $layoutBlockProperties = $reflection->getProperties();

        $genericProperties = new ReflectionClass(LayoutBlock::class);
        foreach ($layoutBlockProperties as $property) {

            if(!$genericProperties->hasProperty($property->getName())){
                $this->revertObject($layoutBlock, $property);
            }
        }

        return $layoutBlock;
    }

    /**
     * @param $object
     * @param $var
     * @param $property
     *
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     * @throws ReflectionException
     */
    public function revertObject($object, \ReflectionProperty $property): mixed
    {

        $reflection = new ReflectionClass($object);

        if ($reflection->implementsInterface(IgnoreRevertInterface::class)) {
            return $object;
        }

        $method = sprintf('get%s', ucfirst((string)$property->getName()));

        if (!$reflection->hasMethod($method)) {
            return $object;
        }

        $var = $object->{$method}();


        if ($var instanceof Collection) {
            if ($var->count() < 1) {
                return $object;
            }

            $newCollection = [];
            foreach ($var as $v) {

                $newVar = $this->revertObjectVars($object, $v);
                $newCollection[] = $newVar;
            }

            $method = sprintf('set%s', ucfirst((string)$property->getName()));
            $reflection = new ReflectionClass($object);

            $persistentCollection = new PersistentCollection($this->_em, $property->class, new ArrayCollection($newCollection));

            if ($reflection->hasMethod($method)) {

                $object->{$method}($persistentCollection);
            }


            return $object;
        }


        if (is_object($var) && $this->_em->getMetadataFactory()->hasMetadataFor($var::class)
        ) {

            $var = $this->revertObjectVars($object, $var);


            $method = sprintf('set%s', ucfirst((string)$property->getName()));
            if ($reflection->hasMethod($method)) {
                $object->{$method}($var);
            }
            $this->_em->persist($var);

            return $object;
        }

        $method = sprintf('set%s', ucfirst((string)$property->getName()));
        if ($reflection->hasMethod($method)) {
            $object->{$method}($var);
        }

        return $object;
    }

    /**
     * @param $parent
     * @param $oldObject
     *
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

        if($oldObject->getId()){
            $object = $this->_em
                ->getRepository($reflection->getName())
                ->findOneBy(['id' => $oldObject->getId()]);

        }


        if (!$object) {
            $object = clone $oldObject;
        }

        if (array_key_exists($object::class, $this->deserializedEntities)) {
            if (array_key_exists(  $object->getId(), $this->deserializedEntities[$object::class] ) ) {
                return $this->deserializedEntities[$object::class][$object->getId(
                )];
            }
        }

        foreach ($reflection->getProperties() as $reflectionProperty) {
            $method = sprintf('get%s', ucfirst($reflectionProperty->getName()));
            if ($reflection->hasMethod($method)) {
                $val = $object->{$method}();
                /** Prevent Recursive loop */


                if ($val !== $parent) {
                    $object = $this->revertObject($object, $reflectionProperty);
                    if($val instanceof PersistentCollection){
                        dump($reflectionProperty->getName());
                        dump($object->{$method}());
                    }
                }


            }
        }

        $this->deserializedEntities[$object::class][$object->getId()] = $object;


        return $object;
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
        return $this->getChildrenQueryBuilder(
            $rootNode,
            false,
            null,
            'asc',
            true
        );
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

    public function getRootNodesQueryBuilder(
        $sortByField = null,
        $direction = 'asc'
    ): QueryBuilder {
        return $this->getChildrenQueryBuilder(
            null,
            true,
            $sortByField,
            $direction
        );
    }

    public function getRootNodesQuery(
        $sortByField = null,
        $direction = 'asc'
    ): Query {
        return $this->getRootNodesQueryBuilder($sortByField, $direction)
            ->getQuery();
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
        $config = $this->listener->getConfiguration(
            $this->_em,
            $meta->getName()
        );
        $alias = 'materialized_path_entity';
        $qb = $this->getQueryBuilder()
            ->select($alias)
            ->from($config['useObjectClass'], $alias);

        $node = new EntityWrapper($node, $this->_em);
        $nodePath = $node->getPropertyValue($config['path']);
        $paths = [];
        $nodePathLength = strlen((string)$nodePath);
        $separatorMatchOffset = 0;
        while ($separatorMatchOffset < $nodePathLength) {
            $separatorPos = strpos(
                (string)$nodePath,
                $config['path_separator'],
                $separatorMatchOffset
            );

            if (false === $separatorPos
                || $separatorPos === $nodePathLength - 1
            ) {
                // last node, done
                $paths[] = $nodePath;
                $separatorMatchOffset = $nodePathLength;
            } elseif (0 === $separatorPos) {
                // path starts with separator, continue
                $separatorMatchOffset = 1;
            } else {
                // add node
                $paths[] = substr(
                    (string)$nodePath,
                    0,
                    $config['path_ends_with_separator'] ? $separatorPos + 1
                        : $separatorPos
                );
                $separatorMatchOffset = $separatorPos + 1;
            }
        }
        $qb->where(
            $qb->expr()->in(
                $alias.'.'.$config['path'],
                $paths
            )
        );
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

    public function getChildrenQueryBuilder(
        $node = null,
        $direct = false,
        $sortByField = null,
        $direction = 'asc',
        $includeNode = false
    ): QueryBuilder {
        $meta = $this->getClassMetadata();
        $config = $this->listener->getConfiguration(
            $this->_em,
            $meta->getName()
        );
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
                        .($config['path_ends_with_separator'] ? '' : $separator)
                        .'%'
                    )
                )
            );

            if ($includeNode) {
                $includeNodeExpr = $qb->expr()->eq(
                    $alias.'.'.$path,
                    $qb->expr()->literal($nodePath)
                );
            } else {
                $expr->add(
                    $qb->expr()->neq(
                        $alias.'.'.$path,
                        $qb->expr()->literal($nodePath)
                    )
                );
            }

            if ($direct) {
                $expr->add(
                    $qb->expr()->orx(
                        $qb->expr()->eq(
                            $alias.'.'.$config['level'],
                            $qb->expr()->literal(
                                $node->getPropertyValue($config['level'])
                            )
                        ),
                        $qb->expr()->eq(
                            $alias.'.'.$config['level'],
                            $qb->expr()->literal(
                                $node->getPropertyValue($config['level']) + 1
                            )
                        )
                    )
                );
            }
        } elseif ($direct) {
            $expr = $qb->expr()->not(
                $qb->expr()->like(
                    $alias.'.'.$path,
                    $qb->expr()->literal(
                        ($config['path_starts_with_separator'] ? $separator
                            : '')
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

        $orderByField = null === $sortByField ? $alias.'.'.$config['path']
            : $alias.'.'.$sortByField;
        $orderByDir = 'asc' === $direction ? 'asc' : 'desc';
        $qb->orderBy($orderByField, $orderByDir);

        return $qb;
    }

    public function getChildrenQuery(
        $node = null,
        $direct = false,
        $sortByField = null,
        $direction = 'asc',
        $includeNode = false
    ): Query {
        return $this->getChildrenQueryBuilder(
            $node,
            $direct,
            $sortByField,
            $direction,
            $includeNode
        )->getQuery();
    }

    public function getChildren(
        $node = null,
        $direct = false,
        $sortByField = null,
        $direction = 'asc',
        $includeNode = false
    ): ?array {
        return $this->getChildrenQuery(
            $node,
            $direct,
            $sortByField,
            $direction,
            $includeNode
        )->execute();
    }

    public function getNodesHierarchyQueryBuilder(
        $node = null,
        $direct = false,
        array $options = [],
        $includeNode = false
    ): QueryBuilder {
        $sortBy = [
            'field' => null,
            'dir' => 'asc',
        ];

        if (isset($options['childSort'])) {
            $sortBy = array_merge($sortBy, $options['childSort']);
        }

        return $this->getChildrenQueryBuilder(
            $node,
            $direct,
            $sortBy['field'],
            $sortBy['dir'],
            $includeNode
        );
    }

    public function getNodesHierarchyQuery(
        $node = null,
        $direct = false,
        array $options = [],
        $includeNode = false
    ): Query {
        return $this->getNodesHierarchyQueryBuilder(
            $node,
            $direct,
            $options,
            $includeNode
        )->getQuery();
    }

    public function getNodesHierarchy(
        $node = null,
        $direct = false,
        array $options = [],
        $includeNode = false
    ): array {
        $meta = $this->getClassMetadata();
        $config = $this->listener->getConfiguration(
            $this->_em,
            $meta->getName()
        );
        $path = $config['path'];

        $nodes = $this->getNodesHierarchyQuery(
            $node,
            $direct,
            $options,
            $includeNode
        )->getArrayResult();
        usort(
            $nodes,
            static fn($a, $b): int => strcmp(
                (string)$a[$path],
                (string)$b[$path]
            )
        );

        return $nodes;
    }

    protected function validate(): bool
    {
        return Strategy::MATERIALIZED_PATH === $this->listener->getStrategy(
            $this->_em,
            $this->getClassMetadata()->name
        )->getName();
    }

    public function getSnapshot($id): ?PageSnapshotInterface
    {
        return $this->_em->getRepository(PageSnapshot::class)->findOneBy(
            ['resourceId' => $id],
            ['version' => 'DESC']
        );
    }
}
