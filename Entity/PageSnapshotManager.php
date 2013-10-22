<?php
/**
 * Created by JetBrains PhpStorm.
 * User: marcbissegger
 * Date: 10/22/13
 * Time: 10:10 AM
 * To change this template use File | Settings | File Templates.
 */

namespace Networking\InitCmsBundle\Entity;

use Networking\InitCmsBundle\Entity\PageSnapshot;
use Doctrine\ORM\EntityRepository;
use Networking\InitCmsBundle\Model\PageSnapshotManagerInterface;
use Doctrine\ORM\EntityManager;


class PageSnapshotManager extends EntityRepository implements PageSnapshotManagerInterface{


    public function __construct(EntityManager $em, $class)
    {
        $classMetaData = $em->getClassMetadata($class);

        parent::__construct($em, $classMetaData);
    }

    public function findSnapshotByPageId($pageId)
    {
        $qb = $this->createQueryBuilder('ps')
            ->where('ps.page = :pageId')
            ->orderBy('ps.version', 'desc')
            ->setParameter(':pageId', $pageId);
        return $qb->getQuery()->execute();
    }

}