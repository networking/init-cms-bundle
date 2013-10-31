<?php
/**
 * This file is part of the init_cms_sandbox package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Document;

use Doctrine\ODM\MongoDB\DocumentManager;
use Networking\InitCmsBundle\Doctrine\UserManager as DoctrineUserManager;
use FOS\UserBundle\Util\CanonicalizerInterface;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;
use Doctrine\ODM\MongoDB\DocumentRepository;

/**
 * Class UserManager
 * @package Networking\InitCmsBundle\Document
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class UserManager extends DoctrineUserManager
{
    /**
     * @var \Doctrine\ODM\MongoDB\DocumentManager
     */
    protected $dm;

    /**
     * @var DocumentRepository $repository
     */
    protected $repository;

    /**
     * @param EncoderFactoryInterface $encoderFactory
     * @param CanonicalizerInterface $usernameCanonicalizer
     * @param CanonicalizerInterface $emailCanonicalizer
     * @param DocumentManager $dm
     * @param string $class
     */
    public function __construct(
        EncoderFactoryInterface $encoderFactory,
        CanonicalizerInterface $usernameCanonicalizer,
        CanonicalizerInterface $emailCanonicalizer,
        DocumentManager $dm,
        $class
    ) {
        parent::__construct($encoderFactory, $usernameCanonicalizer, $emailCanonicalizer, $dm, $class);

        $this->dm = $dm;
    }

    /**
     * @return mixed
     */
    public function getLatestActivity()
    {
        $tenMinutesAgo = new \DateTime('10 hours ago');
        $qb = $this->repository->createQueryBuilder();
        $qb->field('updatedAt')->gte($tenMinutesAgo);

        return $qb->getQuery()->execute();
    }
}
