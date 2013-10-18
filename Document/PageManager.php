<?php

namespace Networking\InitCmsBundle\Document;


use Networking\InitCmsBundle\Model\PageInterface;
use Gedmo\Tree\Document\MongoDB\Repository\MaterializedPathRepository;
use Symfony\Cmf\Component\Routing\ContentRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * PageRepository
 *
 * @author net working AG <info@networking.ch>
 */
/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class PageManager extends MaterializedPathRepository implements ContentRepositoryInterface, ContainerAwareInterface
{


    /**
     * @var ContainerInterface $container
     */
    private $container;

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

        $qb->addOr($qb->expr()->field('isHome')->equals(NULL));
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
                $qb->addAnd($qb->expr()->field('id')->notIn( $childrenIds->toArray()));
            }
            $qb->addAnd($qb->expr()->field('id')->notEqual( $id));
        }

        $qb->addAnd($qb->expr()->field('locale')->notEqual( $locale));
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
}