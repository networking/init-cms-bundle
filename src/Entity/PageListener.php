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
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use App\Entity\Page;
use Networking\InitCmsBundle\Model\PageInterface;
use Networking\InitCmsBundle\Helper\PageHelper;
use Networking\InitCmsBundle\Model\PageListenerInterface;
use Networking\InitCmsBundle\Model\PageManagerInterface;
use Networking\InitCmsBundle\Model\PageSnapshotManagerInterface;

#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::onFlush, priority: -100)]
class PageListener implements PageListenerInterface
{

    /**
     * @var PageManagerInterface
     */
    protected $pageManager;

    /**
     * @var PageSnapshotManagerInterface
     */
    protected $pageSnapshotManager;

    /**
     * PageListener constructor.
     */
    public function __construct(
        PageManagerInterface $pageManager,
        PageSnapshotManagerInterface $pageSnapshotManager
    ) {
        $this->pageManager = $pageManager;
        $this->pageSnapshotManager = $pageSnapshotManager;
    }

    /**
     * @param LifecycleEventArgs $args
     *
     * @return mixed|void
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
            $contentRoute->setPath(
                PageHelper::getPageRoutePath($page->getPath())
            );

            $em->persist($contentRoute);
            $em->getUnitOfWork()->computeChangeSet(
                $em->getClassMetadata($contentRoute::class),
                $contentRoute
            );
        }
    }

    /**
     * @param OnFlushEventArgs $args
     *
     * @return mixed|void
     * @throws \Doctrine\ORM\ORMException
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
                    $contentRoute->setPath(
                        PageHelper::getPageRoutePath($entity->getPath())
                    );

                    $em->persist($contentRoute);
                    $unitOfWork->computeChangeSet(
                        $em->getClassMetadata($contentRoute::class),
                        $contentRoute
                    );
                    foreach ($entity->getAllChildren() as $child) {
                        $contentRoute = $child->getContentRoute();
                        $contentRoute->setPath(
                            PageHelper::getPageRoutePath($child->getPath())
                        );
                        $em->persist($contentRoute);
                        $unitOfWork->computeChangeSet(
                            $em->getClassMetadata($contentRoute::class),
                            $contentRoute
                        );

                        if ($entity->getStatus() == Page::STATUS_PUBLISHED) {
                            if ($childSnapshot = $child->getSnapshot()) {
                                $snapshotRoute
                                    = $childSnapshot->getContentRoute();

                                $newPath = PageHelper::getPageRoutePath(
                                    $child->getPath()
                                );

                                $snapshotRoute->setPath($newPath);
                                $childSnapshot->setPath($newPath);

                                $em->persist($childSnapshot);
                                $em->persist($snapshotRoute);

                                $unitOfWork->computeChangeSet(
                                    $em->getClassMetadata(
                                        $childSnapshot::class
                                    ),
                                    $childSnapshot
                                );
                                $unitOfWork->computeChangeSet(
                                    $em->getClassMetadata(
                                        $snapshotRoute::class
                                    ),
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
