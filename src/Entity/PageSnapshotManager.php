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

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Mapping\ClassMetadata;
use Doctrine\ORM\Mapping\UnderscoreNamingStrategy;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Networking\InitCmsBundle\Model\PageSnapshotManagerInterface;
/**
 * Class PageSnapshotManager.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class PageSnapshotManager extends EntityRepository implements PageSnapshotManagerInterface
{
    /**
     * PageSnapshotManager constructor.
     * @param EntityManagerInterface $om
     * @param $class
     */
    public function __construct(EntityManagerInterface $om, $class)
    {
        $classMetaData = new ClassMetadata($class, new UnderscoreNamingStrategy(numberAware: true));

        parent::__construct($om, $classMetaData);
    }

    /**
     * @param $pageId
     *
     * @return mixed
     */
    public function findSnapshotByPageId($pageId)
    {
        $qb = $this->createQueryBuilder('ps')
            ->where('ps.page = :pageId')
            ->orderBy('ps.version', 'desc')
            ->setParameter(':pageId', $pageId);

        return $qb->getQuery()->execute();
    }

    /**
     * @param $pageId
     *
     * @return mixed
     */
    public function findLastPageSnapshot($pageId)
    {
        $qb = $this->createQueryBuilder('ps')
            ->where('ps.page = :pageId')
            ->orderBy('ps.version', 'desc')
            ->setMaxResults(1)
            ->setParameter(':pageId', $pageId);

        try {
            return $qb->getQuery()->getSingleResult();
        } catch (NoResultException|NonUniqueResultException $e) {
            return null;
        }
    }
}
