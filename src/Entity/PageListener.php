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
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Event\OnFlushEventArgs;
use App\Entity\Page;
use JMS\Serializer\EventDispatcher\EventSubscriberInterface;
use Networking\InitCmsBundle\Model\PageInterface;
use Networking\InitCmsBundle\Helper\PageHelper;
use Networking\InitCmsBundle\Model\PageListenerInterface;
use Networking\InitCmsBundle\Model\PageManagerInterface;
use Networking\InitCmsBundle\Model\PageSnapshotManagerInterface;
use Networking\InitCmsBundle\Serializer\PageSnapshotDeserializationContext;

#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::onFlush, priority: -100)]
class PageListener implements EventSubscriberInterface, PageListenerInterface
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
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            [
                'event' => \JMS\Serializer\EventDispatcher\Events::POST_DESERIALIZE,
                'method' => 'onPostDeserialize',
                'format' => 'json',
            ],
        ];
    }

    public function onPostDeserialize(
        \JMS\Serializer\EventDispatcher\ObjectEvent $event
    ) {
        /** @var $page PageInterface */
        $page = $event->getObject();

        if ($page instanceof PageInterface) {
            $context = $event->getContext();

            if (!$page->getId()) {
                return;
            }

            if ($parent = $page->getParent()) {
                $parent = $this->pageManager->find($page->getParent());
                $page->setParent($parent);
            } else {
                $page->setParent(null);
            }

            if ($alias = $page->getAlias()) {
                $alias = $this->pageManager->find($page->getAlias());
                $page->setAlias($alias);
            } else {
                $page->setAlias(null);
            }

            if ($parents = $page->getParents()) {
                foreach ($parents as $key => $parent) {
                    if (is_array($parent) && array_key_exists('id', $parent)) {
                        $parent = $parent['id'];
                    }
                    $parents[$key] = $this->pageManager->find($parent);
                }

                $page->setParents($parents);
            } else {
                $page->setParents([]);
            }

            if ($children = $page->getChildren()) {
                foreach ($children as $key => $child) {
                    $children[$key] = $this->pageManager->find($child);
                }

                $page->setChildren($children);
            } else {
                $page->setChildren([]);
            }

            if ($originals = $page->getOriginals()) {
                foreach ($originals as $key => $original) {
                    $originals[$key] = $this->pageManager->find($original);
                }

                $page->setOriginals($originals);
            } else {
                $page->setOriginals([]);
            }
            if ($context instanceof PageSnapshotDeserializationContext
                && $context->deserializeTranslations()
            ) {
                if ($translations = $page->getTranslations()) {
                    foreach ($translations as $key => $translation) {
                        $translations[$key] = $this->pageManager->find(
                            $translation
                        );
                    }
                    $page->setTranslations($translations);
                } else {
                    $originalPageId = $page->getId();
                    $originalPage = $this->pageManager->find($originalPageId);
                    $page->setTranslations($originalPage->getAllTranslations());
                }
            }

            if (!$contentRoute = $page->getContentRoute()->getId()) {
                $lastPageSnapshot
                    = $this->pageSnapshotManager->findLastPageSnapshot(
                        $page->getId()
                    );

                if ($lastPageSnapshot) {
                    $page->setContentRoute(
                        $lastPageSnapshot->getContentRoute()
                    );
                }
            }
        }
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
        $page = $args->getEntity();

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
