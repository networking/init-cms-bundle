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

use Doctrine\ORM\EntityManager;
use Gedmo\Tree\Entity\Repository\MaterializedPathRepository;
use Networking\InitCmsBundle\Model\PageInterface;
use Networking\InitCmsBundle\Model\PageManagerInterface;


/**
 * Class PageManager
 * @package Networking\InitCmsBundle\Entity
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class PageManager extends MaterializedPathRepository implements PageManagerInterface
{

    /**
     * @param EntityManager $om
     * @param \Doctrine\ORM\Mapping\ClassMetadata $class
     */
    public function __construct(EntityManager $om, $class)
    {
        if (class_exists($class)) {
            $classMetaData = $om->getClassMetadata($class);

            parent::__construct($om, $classMetaData);
        }

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
     * @param null $id
     * @param bool $showHome
     * @param bool $showChildren
     * @return \Doctrine\ORM\QueryBuilder|mixed
     */
    public function getParentPagesQuery($locale, $id = null, $showHome = false, $showChildren = false)
    {
        $qb = $this->createQueryBuilder('p');
        if(!$showHome){
            $qb->where($qb->expr()->isNull('p.isHome') . ' OR p.isHome <> 1');
        }
        if ($id) {
            if (!$showChildren) {
                /** @var $page PageInterface */
                $page = $this->find($id);
                $childrenIds = $page->getChildren()->map(
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


    /**
     * @param PageInterface $page
     * @return mixed
     */
    public function save(PageInterface $page)
    {
        if (!$page->getId()) {
            $page->setContentRoute(new ContentRoute());
        }
        $this->_em->persist($page);
    }
}
