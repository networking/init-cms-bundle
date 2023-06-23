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
namespace Networking\InitCmsBundle\Doctrine\Extensions\Versionable;

use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Networking\InitCmsBundle\Entity\ResourceVersion;
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;

/**
 * Class VersionListener.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
#[AsDoctrineListener(event: \Doctrine\ORM\Events::postPersist)]
#[AsDoctrineListener(event: \Doctrine\ORM\Events::postUpdate)]
class VersionListener
{
    public function postPersist(PostPersistEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getObjectManager();
        if ($entity instanceof VersionableInterface) {
            if (!$entity->hasListener()) {
                $this->makeSnapshot($entity, $em);
            }
        }
    }

    public function postUpdate(PostUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getObjectManager();
        if ($entity instanceof VersionableInterface) {

            if (!$entity->hasListener()) {
                $this->makeSnapshot($entity, $em);
            }
        }
    }

    /**
     * @param $entity
     */
    private function makeSnapshot($entity, \Doctrine\ORM\EntityManager $em)
    {
        $resourceVersion = new ResourceVersion($entity);
        $class = $em->getClassMetadata($resourceVersion::class);

        $em->persist($resourceVersion);
        $em->getUnitOfWork()->computeChangeSet($class, $resourceVersion);
    }
}
