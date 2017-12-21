<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Doctrine\Extensions\Versionable;

use Networking\InitCmsBundle\Entity\ResourceVersion;
use Doctrine\ORM\Event\LifecycleEventArgs;

/**
 * Class VersionListener
 * @package Networking\InitCmsBundle\Doctrine\Extensions\Versionable
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class VersionListener
{

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
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
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
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
     * @param \Doctrine\ORM\EntityManager $em
     */
    private function makeSnapshot($entity, \Doctrine\ORM\EntityManager $em)
    {
        $resourceVersion = new ResourceVersion($entity);
        $class = $em->getClassMetadata(get_class($resourceVersion));

        $em->persist($resourceVersion);
        $em->getUnitOfWork()->computeChangeSet($class, $resourceVersion);
    }
}
