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
use Doctrine\Persistence\ObjectManager;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Query;
use Gedmo\Tree\Entity\Repository\MaterializedPathRepository;
use Networking\InitCmsBundle\Model\PageInterface;
use Networking\InitCmsBundle\Model\PageManagerInterface;
use Networking\InitCmsBundle\Model\LayoutBlock;

/**
 * Class PageManager.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class PageManager extends MaterializedPathRepository implements PageManagerInterface
{
    /**
     * PageManager constructor.
     * @param ObjectManager $om
     * @param $class
     */
    public function __construct(ObjectManager $om, $class)
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
     * @param int    $hydrationMode
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
            ->where($qb->expr()->eq(
                'ps.id',
                '('.$qb2->select('MAX(pp.id)')
                    ->where('p.id = pp.page')
                    ->getDQL().')'
            ))
            ->orWhere('ps.id IS NULL')
            ->orderBy('p.'.$sort,  $order);

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
     * @param PageInterface              $draftPage
     * @param \JMS\Serializer\Serializer $serializer
     *
     * @return PageInterface
     */
    public function revertToPublished(PageInterface $draftPage, \JMS\Serializer\SerializerInterface $serializer)
    {
        $pageSnapshot = $draftPage->getSnapshot();
        $contentRoute = $draftPage->getContentRoute();

        /** @var $publishedPage PageInterface */
        $publishedPage = $serializer->deserialize(
            $pageSnapshot->getVersionedData(),
            $this->getClassName(),
            'json'
        );

        // Save the layout blocks in a temp variable so that we can
        // assure the correct layout blocks will be saved and not
        // merged with the layout blocks from the draft page
        $tmpLayoutBlocks = $publishedPage->getLayoutBlock();

	    // tell the entity manager to handle our published page
	    // as if it came from the DB and not a serialized object
	    $publishedPage = $this->_em->merge($publishedPage);



        $contentRoute->setTemplate($pageSnapshot->getContentRoute()->getTemplate());
        $contentRoute->setTemplateName($pageSnapshot->getContentRoute()->getTemplateName());
        $contentRoute->setController($pageSnapshot->getContentRoute()->getController());
        $contentRoute->setPath($pageSnapshot->getContentRoute()->getPath());

        $this->_em->merge($contentRoute);

        $publishedPage->setContentRoute($contentRoute);

        // Set the layout blocks of the NOW managed entity to
        // exactly that of the published version
	    foreach ($tmpLayoutBlocks as $key => $layoutBlock){

		    try{
			    $layoutBlock = $this->_em->merge($layoutBlock);
		    }catch (EntityNotFoundException $e){
		    	$layoutBlock = clone $layoutBlock;
		    	$this->_em->persist($layoutBlock);
		    }

		    $this->resetContent($publishedPage, $layoutBlock, $serializer);

		    $tmpLayoutBlocks->set($key, $layoutBlock);
	    }

	    $publishedPage->resetLayoutBlock($tmpLayoutBlocks);
	    $this->_em->persist($publishedPage);
        $this->_em->flush();

        return $publishedPage;
    }

	/**
	 * @param LayoutBlock $layoutBlock
	 *
	 * @return LayoutBlock
	 * @throws \Doctrine\ORM\ORMException
	 * @throws \Doctrine\ORM\OptimisticLockException
	 * @throws \ReflectionException
	 */
    public function resetContent(PageInterface $page, LayoutBlock $layoutBlock, \JMS\Serializer\SerializerInterface $serializer)
    {
	    if ($contentObject = $layoutBlock->getSnapshotContent()) {
		    $contentObject = $serializer->deserialize($contentObject, $layoutBlock->getClassType(), 'json');



		    try {
			    $contentObject = $this->_em->merge($contentObject);
			    $reflection = new \ReflectionClass($contentObject);
			    foreach ($reflection->getProperties() as $property) {
				    $method = sprintf('get%s', ucfirst($property->getName()));
				    if ($reflection->hasMethod($method) && $var = $contentObject->{$method}()) {
					    if ($var instanceof ArrayCollection) {
						    foreach ($var as $key =>  $v) {
							    $v = $this->_em->merge($v);

							    $var->set($key, $v);
						    }
						    $method = sprintf('set%s', ucfirst($property->getName()));
						    $contentObject->{$method}($var);
					    }

					    if(is_object($var) && $this->_em->getMetadataFactory()->hasMetadataFor(get_class($var))){
						    $var = $this->_em->merge($var);

						    $method = sprintf('set%s', ucfirst($property->getName()));
						    $contentObject->{$method}($var);
					    }
				    }
			    }

			    $this->_em->persist($contentObject);
			    $this->_em->flush($contentObject);

		    } catch (EntityNotFoundException $e) {
			    $classType = $layoutBlock->getClassType();
			    $newContentObject = clone $contentObject;
			    $reflection = new \ReflectionClass($contentObject);
			    foreach ($reflection->getProperties() as $property) {
				    $method = sprintf('get%s', ucfirst($property->getName()));
				    if ($reflection->hasMethod($method) && $var = $contentObject->{$method}()) {
					    if ($var instanceof ArrayCollection) {
						    foreach ($var as $key =>  $v) {
							    $v = $this->_em->merge($v);

							    $var->set($key, $v);
						    }
						    $method = sprintf('set%s', ucfirst($property->getName()));
						    $newContentObject->{$method}($var);
					    }

					    if(is_object($var) && $this->_em->getMetadataFactory()->hasMetadataFor(get_class($var))){
						    $var = $this->_em->merge($var);

						    $method = sprintf('set%s', ucfirst($property->getName()));
						    $newContentObject->{$method}($var);
					    }

				    }
			    }
			    $this->_em->persist($newContentObject);
			    $this->_em->flush($newContentObject);


			    $layoutBlock->setObjectId($newContentObject->getId());
		    }

	    }

	    $layoutBlock->setPage($page);

	    $this->_em->persist($layoutBlock);

	    $this->_em->flush($layoutBlock);

	    return $layoutBlock;
    }

    public function revertObject($object, $var, $property){
	    if(is_object($var) && $this->_em->getMetadataFactory()->hasMetadataFor(get_class($var))){
		    $var = $this->_em->merge($var);

		    $method = sprintf('set%s', ucfirst($property->getName()));
		    $object->{$method}($var);
	    }
	    return $object;
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
    }

    public function resetEntityManager($em)
    {
        $this->_em = $em;
    }
}
