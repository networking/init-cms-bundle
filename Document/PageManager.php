<?php

namespace Networking\InitCmsBundle\Document;


use Doctrine\ODM\MongoDB\DocumentManager;
use Networking\InitCmsBundle\Model\PageInterface;
use Gedmo\Tree\Document\MongoDB\Repository\MaterializedPathRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Networking\InitCmsBundle\Model\PageManagerInterface;

/**
 * PageRepository
 *
 * @author net working AG <info@networking.ch>
 */
/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class PageManager extends MaterializedPathRepository implements PageManagerInterface
{


    /**
     * @var ContainerInterface $container
     */
    private $container;


    public function __construct(DocumentManager $om, $class)
    {

        $classMetaData = $om->getClassMetadata($class);
        $uow = $om->getUnitOfWork();

        parent::__construct($om, $uow, $classMetaData);
    }

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @param  string $id
     * @return object
     */
    public function findById($id)
    {
        return $this->find($id);
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
     * @param $locale
     * @param  null $id
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getParentPagesQuery($locale, $id = null)
    {
        $qb = $this->createQueryBuilder();

        $qb->addOr($qb->expr()->field('isHome')->equals(null));
        $qb->addOr($qb->expr()->field('isHome')->notEqual(1));

        if ($id) {
            /** @var $page PageInterface */
            $page = $this->find($id);

            /** @var \Doctrine\Common\Collections\ArrayCollection $childrenIds */
            $childrenIds = $page->getChildren()->map(
                function (PageInterface $p) {
                    return $p->getId();
                }
            );

            if ($childrenIds->count()) {
                $qb->addAnd($qb->expr()->field('id')->notIn($childrenIds->toArray()));
            }
            $qb->addAnd($qb->expr()->field('id')->notEqual($id));
        }

        $qb->addAnd($qb->expr()->field('locale')->notEqual($locale));
        $qb->sort('path', 'ASC');


        return $qb;
    }

    /**
     * @param $locale
     * @param null $id
     * @return mixed
     */
    public function getParentPagesChoices($locale, $id = null)
    {
        $qb = $this->getParentPagesQuery($locale, $id);

        return $qb->getQuery()->execute();
    }

    public function getAllSortBy($sort, $order = 'DESC')
    {
        $qb = $this->createQueryBuilder()
            ->sort($sort, $order);

        return $qb->getQuery()->execute();
    }

    /**
     * @param $draftPage
     * @param $serializer
     * @return mixed
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
        $publishedPage = $this->dm->merge($publishedPage);


        $contentRoute->setTemplate($pageSnapshot->getContentRoute()->getTemplate());
        $contentRoute->setTemplateName($pageSnapshot->getContentRoute()->getTemplateName());
        $contentRoute->setController($pageSnapshot->getContentRoute()->getController());
        $contentRoute->setPath($pageSnapshot->getContentRoute()->getPath());

        $this->dm->merge($contentRoute);

        $publishedPage->setContentRoute($contentRoute);

        // Set the layout blocks of the NOW managed entity to
        // exactly that of the published version
        $publishedPage->resetLayoutBlock($tmpLayoutBlocks);
        $this->dm->flush();

        return $publishedPage;
    }

    /**
     * @param PageInterface $page
     * @return mixed
     */
    public function save(PageInterface $page)
    {
        if (!$page->getId()) {
            $page->setContentRoute(new ContentRoute());
        }
        $this->dm->persist($page);
    }

    /**
     * @param LayoutBlock $layoutBlock
     * @return mixed
     */
    public function findByLayoutBlock(LayoutBlock $layoutBlock)
    {
        $id = $layoutBlock->getId();

        $qb = $this->createQueryBuilder();

        $qb->field('layoutBlock.$id')->equals($id);

        return $qb->getQuery()->execute();
    }
}