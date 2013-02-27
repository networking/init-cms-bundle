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

/**
 * ContentRepository
 *
 * @author net working AG <info@networking.ch>
 */
class UserRepository extends EntityRepository
{
    public function getLatestActivity()
    {
        $tenMinutesAgo = new \DateTime('- 10 minutes');
        $qb = $this->createQueryBuilder('u');
        $qb->where('u.updatedAt >= :datetime')
            ->setParameter(':datetime', $tenMinutesAgo );

        return $qb->getQuery()->getResult();
    }
}
