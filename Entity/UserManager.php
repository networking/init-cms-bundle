<?php
/**
 * This file is part of the init_cms_sandbox package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Entity;

use Doctrine\ORM\EntityManager;
use Networking\InitCmsBundle\Doctrine\UserManager as DoctrineUserManager;
use FOS\UserBundle\Util\CanonicalizerInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

/**
 * Class UserManager
 * @package Networking\InitCmsBundle\Entity
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class UserManager extends DoctrineUserManager
{
    /**
     * @var \Doctrine\ORM\EntityManager
     */
    protected $em;

    /**
     * @param EncoderFactoryInterface $encoderFactory
     * @param CanonicalizerInterface $usernameCanonicalizer
     * @param CanonicalizerInterface $emailCanonicalizer
     * @param EntityManager $em
     * @param string $class
     */
    public function __construct(
        EncoderFactoryInterface $encoderFactory,
        CanonicalizerInterface $usernameCanonicalizer,
        CanonicalizerInterface $emailCanonicalizer,
        EntityManager $em,
        $class
    ) {
        parent::__construct($encoderFactory, $usernameCanonicalizer, $emailCanonicalizer, $em, $class);

        $this->em = $em;
    }

    /**
     * @return mixed
     */
    public function getLatestActivity()
        {
            $tenMinutesAgo = new \DateTime('- 10 minutes');
            $qb = $this->repository->createQueryBuilder('u');
            $qb->where('u.updatedAt >= :datetime')
                ->setParameter(':datetime', $tenMinutesAgo);

            return $qb->getQuery()->getResult();
        }
}
