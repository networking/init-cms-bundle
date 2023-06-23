<?php

declare(strict_types=1);

/**
 * This file is part of the init_cms_sandbox package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Entity;

use Networking\InitCmsBundle\Doctrine\UserManager as DoctrineUserManager;
use Doctrine\ORM\EntityManagerInterface;
use Networking\InitCmsBundle\Model\UserInterface;
use Sonata\UserBundle\Util\CanonicalFieldsUpdaterInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * Class UserManager.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class UserManager extends DoctrineUserManager
{

    /**
     * {@inheritdoc}
     */
    public function updateUser(UserInterface $user, $andFlush = true)
    {
        $this->save($user, $andFlush);
    }
    /**
     * @return mixed
     */
    public function getLatestActivity()
    {
        $tenMinutesAgo = new \DateTime('- 10 minutes');
        $qb = $this->registry->getRepository($this->getClass())->createQueryBuilder('u');
        $qb->where('u.updatedAt >= :datetime')
            ->setParameter(':datetime', $tenMinutesAgo);

        return $qb->getQuery()->getResult();
    }
}
