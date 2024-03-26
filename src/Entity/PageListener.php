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

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use Networking\InitCmsBundle\Doctrine\Extensions\Versionable\VersionableInterface;
use Networking\InitCmsBundle\Model\PageInterface;
use Networking\InitCmsBundle\Helper\PageHelper;
use Networking\InitCmsBundle\Model\PageListener as ModelPageListener;

#[AsDoctrineListener(event: Events::postPersist, priority: -100)]
#[AsDoctrineListener(event: Events::onFlush, priority: -100)]
/**
 * Class PageListener.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class PageListener extends ModelPageListener
{

    /**
     * @param PostPersistEventArgs $args
     *
     * @return void
     * @throws \Doctrine\ORM\ORMException
     */
    public function postPersist(PostPersistEventArgs $args): void
    {

        /** @var PageInterface $page */
        $page = $args->getObject();

        if (!$page instanceof PageInterface) {
            return;
        }

        /** @var EntityManager $em */
        $em = $args->getObjectManager();

        if ($contentRoute = $page->getContentRoute()) {
            $contentRoute->setObjectId($page->getId());
            $contentRoute->setPath(PageHelper::getPageRoutePath($page->getPath()));

            $em->persist($contentRoute);
            $em->getUnitOfWork()->computeChangeSet($em->getClassMetadata($contentRoute::class), $contentRoute);
        }
    }

    /**
     * @param OnFlushEventArgs $args
     *
     * @return void
     */
    public function onFlush(OnFlushEventArgs $args): void
    {
        $em = $args->getObjectManager();

        $unitOfWork = $em->getUnitOfWork();


        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {
            if ($entity instanceof PageInterface) {

                if ($contentRoute = $entity->getContentRoute()) {
                    $em->refresh($contentRoute);
                    $contentRoute->setObjectId($entity->getId());
                    $contentRoute->setPath(PageHelper::getPageRoutePath($entity->getPath()));

                    $em->persist($contentRoute);
                    $unitOfWork->computeChangeSet($em->getClassMetadata($contentRoute::class), $contentRoute);
                    foreach ($entity->getAllChildren() as $child) {
                        $contentRoute = $child->getContentRoute();
                        $contentRoute->setPath(PageHelper::getPageRoutePath($child->getPath()));
                        $em->persist($contentRoute);
                        $unitOfWork->computeChangeSet($em->getClassMetadata($contentRoute::class), $contentRoute);

                        if ($entity->getStatus() == VersionableInterface::STATUS_PUBLISHED) {
                            if ($childSnapshot = $child->getSnapshot()) {
                                $snapshotRoute = $childSnapshot->getContentRoute();

                                $newPath = PageHelper::getPageRoutePath($child->getPath());

                                $snapshotRoute->setPath($newPath);
                                $childSnapshot->setPath($newPath);

                                $em->persist($childSnapshot);
                                $em->persist($snapshotRoute);

                                $unitOfWork->computeChangeSet(
                                    $em->getClassMetadata($childSnapshot::class),
                                    $childSnapshot
                                );
                                $unitOfWork->computeChangeSet(
                                    $em->getClassMetadata($snapshotRoute::class),
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
