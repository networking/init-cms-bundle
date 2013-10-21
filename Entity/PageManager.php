<?php

namespace Networking\InitCmsBundle\Entity;

use Doctrine\ORM\EntityManager;
use Networking\InitCmsBundle\Entity\Page;

use Gedmo\Tree\Entity\Repository\MaterializedPathRepository;
use Networking\InitCmsBundle\Model\PageInterface;
use Networking\InitCmsBundle\Model\PageManagerInterface;
use Networking\InitCmsBundle\Model\PageSnapshotInterface;
use Symfony\Cmf\Component\Routing\ContentRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * PageRepository
 *
 * @author net working AG <info@networking.ch>
 */
class PageManager extends MaterializedPathRepository implements PageManagerInterface
{

    /**
     * @var ContainerInterface $container
     */
    private $container;


    public function __construct(EntityManager $om, $class)
    {

        $classMetaData = $om->getClassMetadata($class);

        parent::__construct($om, $classMetaData);
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
     * @param $locale
     * @param  null $id
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getParentPagesQuery($locale, $id = null)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->where($qb->expr()->isNull('p.isHome') . ' OR p.isHome <> 1');
        if ($id) {
            /** @var $page Page */
            $page = $this->find($id);
            $childrenIds = $page->getChildren()->map(
                function (Page $p) {
                    return $p->getId();
                }
            );

            if ($childrenIds->count()) {
                $qb->where($qb->expr()->notIn('p.id', $childrenIds->toArray()));
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
     * @return mixed
     */
    public function getParentPagesChoices($locale, $id = null)
    {
        $qb = $this->getParentPagesQuery($locale, $id);

        return $qb->getQuery()->execute();
    }

    public function getAllSortBy($sort, $order = 'DESC')
    {
        $qb = $this->createQueryBuilder('p')
            ->orderBy('p.' . $sort, $order);

        return $qb->getQuery()->execute();
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
        $publishedPage->resetLayoutBlock($tmpLayoutBlocks);
        $this->_em->flush();

        return $publishedPage;
    }
}
