<?php

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Document;

use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs,
    Symfony\Component\HttpFoundation\Session\Session,
    Symfony\Component\DependencyInjection\ContainerInterface,
    Networking\InitCmsBundle\Model\PageInterface,
    Networking\InitCmsBundle\Helper\PageHelper,
    JMS\Serializer\EventDispatcher\EventSubscriberInterface,
    JMS\Serializer\EventDispatcher\Event;
use Doctrine\ODM\MongoDB\Event\OnFlushEventArgs;
use Doctrine\ODM\MongoDB\Event\PostFlushEventArgs;
use Networking\InitCmsBundle\Model\LayoutBlockInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

/**
 * @author net working AG <info@networking.ch>
 */
class PageListener implements EventSubscriberInterface
{

    /**
     * @var \Symfony\Component\HttpFoundation\Session\Session $session
     */
    protected $session;

    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    protected $container;

    /**
     * @param \Symfony\Component\HttpFoundation\Session\Session $session
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(Session $session, ContainerInterface $container)
    {
        $this->session = $session;
        $this->container = $container;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            array(
                'event' => 'serializer.post_deserialize',
                'method' => 'onPostDeserialize',
                'format' => 'json'
            ),
        );
    }


    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getDocument();

        $em = $args->getDocumentManager();

        if ($entity instanceof PageInterface) {

            if ($contentRoute = $entity->getContentRoute()) {

                $contentRoute->setObjectId($entity->getId());
                $contentRoute->setPath(PageHelper::getPageRoutePath($entity->getPath()));
                $em->persist($contentRoute);
                $em->getUnitOfWork()->computeChangeSet($em->getClassMetadata(get_class($contentRoute)), $contentRoute);
            }
        }
    }

    public function onFlush(OnFlushEventArgs $args)
    {
        $em = $args->getDocumentManager();
        $unitOfWork = $em->getUnitOfWork();

        foreach ($unitOfWork->getScheduledDocumentUpdates() as $entity) {

            if ($entity instanceof PageInterface) {
                if ($contentRoute = $entity->getContentRoute()) {
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

    /**
     * @param \JMS\Serializer\EventDispatcher\Event $event
     */
    public function onPostDeserialize(\JMS\Serializer\EventDispatcher\ObjectEvent $event)
    {


        /** @var $page PageInterface */
        $page = $event->getObject();


        if ($page instanceof PageInterface) {
            $er = $this->container->get('networking_init_cms.page_manager');

            if ($parent = $page->getParent()) {

                $parent = $er->find($page->getParent());
                $page->setParent($parent);
            } else {
                $page->setParent(null);
            }

            if ($parents = $page->getParents()) {
                foreach ($parents as $key => $parent) {
                    $parents[$key] = $er->find($parent);
                }

                $page->setParents($parents);
            } else {
                $page->setParents(array());
            }

            if ($children = $page->getChildren()) {
                foreach ($children as $key => $child) {
                    $children[$key] = $er->find($child);
                }

                $page->setChildren($children);
            } else {
                $page->setChildren(array());
            }

            if ($originals = $page->getOriginals()) {
                foreach ($originals as $key => $original) {
                    $originals[$key] = $er->find($original);
                }

                $page->setOriginals($originals);
            } else {
                $page->setOriginals(array());
            }
            if ($translations = $page->getTranslations()) {
                foreach ($translations as $key => $translation) {
                    $translations[$key] = $er->find($translation);
                }
                $page->setTranslations($translations);
            } else {
                $originalPageId = $page->getId();
                $originalPage = $er->find($originalPageId);
                $page->setTranslations($originalPage->getAllTranslations()->toArray());
            }

            if (!$contentRoute = $page->getContentRoute()) {
                $originalPageId = $page->getId();
                $originalPage = $er->find($originalPageId);
                $page->setContentRoute($originalPage->getContentRoute());
            }
        }

    }
}
