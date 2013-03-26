<?php

namespace Networking\InitCmsBundle\Entity;

use Networking\InitCmsBundle\Entity\Page;

use Gedmo\Tree\Entity\Repository\MaterializedPathRepository;
use Symfony\Cmf\Component\Routing\ContentRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
/**
 * PageRepository
 *
 * @author net working AG <info@networking.ch>
 */
class PageRepository extends MaterializedPathRepository implements ContentRepositoryInterface, ContainerAwareInterface
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
     * @param $locale
     * @param  null                       $id
     * @return \Doctrine\ORM\QueryBuilder
     */
    public function getParentPagesQuery($locale, $id = null)
    {
        $qb = $this->createQueryBuilder('p');
        $qb->where($qb->expr()->isNull('p.isHome').' OR p.isHome <> 1');
        if ($id) {
            /** @var $page Page */
            $page = $this->find($id);
            $childrenIds = $page->getChildren()->map(function(Page $p) {
                return $p->getId();
            });

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
            ->orderBy('p.'.$sort, $order);
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
}
