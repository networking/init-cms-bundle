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

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Networking\InitCmsBundle\Model\Page;
use Networking\InitCmsBundle\Model\PageInterface;
use Networking\InitCmsBundle\Helper\PageHelper;
use Networking\InitCmsBundle\Model\PageListener as ModelPageListener;

/**
 * Class PageListener.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class PageListener extends ModelPageListener
{
    /**
     * @param LifecycleEventArgs $args
     * @return mixed|void
     * @throws \Doctrine\ORM\ORMException
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        /** Do not need to set content route properties here as done in onFlush event */
        $entity = $args->getObject();

        /** @var EntityManager $em */
        $em = $args->getObjectManager();

        if ($entity instanceof PageInterface) {

            if ($contentRoute = $entity->getContentRoute()) {
                $contentRoute->setObjectId($entity->getId());
                $contentRoute->setPath(PageHelper::getPageRoutePath($entity->getPath()));

                $em->persist($contentRoute);
                $em->getUnitOfWork()->computeChangeSet($em->getClassMetadata(get_class($contentRoute)), $contentRoute);
            }
        }
    }

    /**
     * @param OnFlushEventArgs $args
     * @return mixed|void
     * @throws \Doctrine\ORM\ORMException
     */
    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getEntityManager();

        $unitOfWork = $em->getUnitOfWork();


        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof PageInterface) {

                if ($contentRoute = $entity->getContentRoute()) {
                    $em->refresh($contentRoute);
                    $contentRoute->setObjectId($entity->getId());
                    $contentRoute->setPath(PageHelper::getPageRoutePath($entity->getPath()));

                    $em->persist($contentRoute);
                    $unitOfWork->computeChangeSet($em->getClassMetadata(get_class($contentRoute)), $contentRoute);
                    foreach ($entity->getAllChildren() as $child) {
                        $contentRoute = $child->getContentRoute();
                        $contentRoute->setPath(PageHelper::getPageRoutePath($child->getPath()));
                        $em->persist($contentRoute);
                        $unitOfWork->computeChangeSet($em->getClassMetadata(get_class($contentRoute)), $contentRoute);

                        if ($entity->getStatus() == Page::STATUS_PUBLISHED) {
                            if ($childSnapshot = $child->getSnapshot()) {
                                $snapshotRoute = $childSnapshot->getContentRoute();

                                $newPath = PageHelper::getPageRoutePath($child->getPath());

                                $snapshotRoute->setPath($newPath);
                                $childSnapshot->setPath($newPath);

                                $em->persist($childSnapshot);
                                $em->persist($snapshotRoute);

                                $unitOfWork->computeChangeSet(
                                    $em->getClassMetadata(get_class($childSnapshot)),
                                    $childSnapshot
                                );
                                $unitOfWork->computeChangeSet(
                                    $em->getClassMetadata(get_class($snapshotRoute)),
                                    $snapshotRoute
                                );
                            }
                        }
                    }
                }
            }
        }
    }
}
