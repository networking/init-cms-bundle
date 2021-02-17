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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Doctrine\ORM\Query;
use Doctrine\ORM\TransactionRequiredException;
use Gedmo\Tree\Entity\Repository\MaterializedPathRepository;
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
class PageManager extends MaterializedPathRepository implements PageManagerInterface
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
    public function findById($id)
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
    public function getParentPagesQuery($locale, $id = null, $showHome = false, $showChildren = false)
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
                    function (PageInterface $p) {
                        return $p->getId();
                    }
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
     *
     * @return mixed
     */
    public function getParentPagesChoices($locale, $id = null)
    {
        $qb = $this->getParentPagesQuery($locale, $id);

        return $qb->getQuery()->execute();
    }

    /**
     * @param $sort
     * @param string $order
     * @param int $hydrationMode
     *
     * @return mixed
     */
    public function getAllSortBy($sort, $order = 'DESC', $hydrationMode = Query::HYDRATE_OBJECT)
    {
        $query = $this->getAllSortByQuery($sort, $order);

        return $query->execute([], $hydrationMode);
    }

    /**
     * @param $sort
     * @param string $order
     *
     * @return Query
     */
    public function getAllSortByQuery($sort, $order = 'DESC')
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
    public function getContentId($content)
    {
        return $content->getId();
    }

    /**
     * @param PageInterface $draftPage
     * @param \JMS\Serializer\Serializer $serializer
     *
     * @return PageInterface
     */
    public function revertToPublished(PageInterface $draftPage, SerializerInterface $serializer)
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
                function (LayoutBlock $layoutBlock) use ($publishedlayoutBlock) {
                    return $publishedlayoutBlock->getId() === $layoutBlock->getId();
                }
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
     * @param LayoutBlock $layoutBlock
     *
     * @return LayoutBlock
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws ReflectionException
     */
    public function resetContent(LayoutBlock $layoutBlock, SerializerInterface $serializer)
    {

        if ($snapshotContent = $layoutBlock->getSnapshotContent()) {

            $publishedContent = $serializer->deserialize($snapshotContent, $layoutBlock->getClassType(), 'json');

            $reflection = new ReflectionClass($publishedContent);
            foreach ($reflection->getProperties() as $property) {
                $this->revertObject($publishedContent, $property);
            }

            $this->_em->persist($publishedContent);
            $this->_em->flush($publishedContent);

            $layoutBlock->setObjectId($publishedContent->getId());

        }

        return $layoutBlock;
    }

    /**
     * @param $object
     * @param $var
     * @param $property
     * @return mixed
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     * @throws ReflectionException
     */
    public function revertObject($object, $property)
    {
        $reflection = new ReflectionClass($object);

        if ($reflection->implementsInterface(IgnoreRevertInterface::class)) {
            dump($object);
            return $object;
        }

        $method = sprintf('get%s', ucfirst($property->getName()));

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
            $method = sprintf('set%s', ucfirst($property->getName()));
            $reflection = new ReflectionClass($object);
            if ($reflection->hasMethod($method)) {
                $object->{$method}($newCollection);
            }

            return $object;

        }

        if (is_object($var) && $this->_em->getMetadataFactory()->hasMetadataFor(get_class($var))) {

            $newVar = $this->revertObjectVars($object, $var);
            $this->_em->persist($newVar);

            $method = sprintf('set%s', ucfirst($property->getName()));
            if ($reflection->hasMethod($method)) {
                $object->{$method}($newVar);
            }

            return $object;

        }

        $method = sprintf('set%s', ucfirst($property->getName()));
        if ($reflection->hasMethod($method)) {
            $object->{$method}($var);
        }

        return $object;
    }

    /**
     * @param $parent
     * @param $oldObject
     * @return object|null
     * @throws ORMException
     * @throws OptimisticLockException
     * @throws TransactionRequiredException
     * @throws ReflectionException
     */
    public function revertObjectVars($parent, $oldObject)
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

    /**
     * @param Collection $currentLayoutBlocks
     * @param array $layoutBlockIds
     * @return Collection
     */
    public function cleanLayoutBlocks(Collection $currentLayoutBlocks, array $layoutBlockIds)
    {

        $blocksToRemove = $currentLayoutBlocks->filter(
            function (LayoutBlock $oldBlock) use ($layoutBlockIds) {
                return !in_array($oldBlock->getId(), $layoutBlockIds);
            }
        );

        foreach ($blocksToRemove as $deadBlock) {
            $currentLayoutBlocks->removeElement($deadBlock);
        }

        return $currentLayoutBlocks;

    }

    /**
     * @param PageInterface $page
     *
     * @return mixed
     */
    public function save(PageInterface $page)
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
}
