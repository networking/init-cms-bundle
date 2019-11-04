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

use Networking\InitCmsBundle\Doctrine\UserManager as DoctrineUserManager;
use Doctrine\Common\Persistence\ObjectManager;
use FOS\UserBundle\Util\CanonicalFieldsUpdater;
use FOS\UserBundle\Util\PasswordUpdaterInterface;

/**
 * Class UserManager.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class UserManager extends DoctrineUserManager
{
    /**
     * UserManager constructor.
     *
     * @param PasswordUpdaterInterface $passwordUpdater
     * @param CanonicalFieldsUpdater   $canonicalFieldsUpdater
     * @param ObjectManager            $om
     * @param $class
     */
    public function __construct(PasswordUpdaterInterface $passwordUpdater, CanonicalFieldsUpdater $canonicalFieldsUpdater, ObjectManager $om, $class)
    {
        if (class_exists($class)) {
            parent::__construct($passwordUpdater, $canonicalFieldsUpdater, $om, $class);
        }
    }

    /**
     * @return mixed
     */
    public function getLatestActivity()
    {
        $tenMinutesAgo = new \DateTime('- 10 minutes');
        $qb = $this->objectManager->getRepository($this->getClass())->createQueryBuilder('u');
        $qb->where('u.updatedAt >= :datetime')
            ->setParameter(':datetime', $tenMinutesAgo);

        return $qb->getQuery()->getResult();
    }
}
