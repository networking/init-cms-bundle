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

use Doctrine\ORM\EntityRepository;
use Networking\InitCmsBundle\Model\PageSnapshotRepositoryInterface;

/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class PageSnapshotRepository extends EntityRepository implements PageSnapshotRepositoryInterface
{


    public function findSnapshotByPageId($pageId)
    {
        $qb = $this->createQueryBuilder('ps')
                        ->where('ps.page = :pageId')
                        ->orderBy('ps.version', 'desc')
                        ->setParameter(':pageId', $pageId);
         return $qb->getQuery()->execute();
    }
}
