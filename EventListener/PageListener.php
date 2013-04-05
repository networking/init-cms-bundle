<?php

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs,
    Symfony\Component\HttpFoundation\Session\Session,
    Symfony\Component\DependencyInjection\ContainerInterface,
    Networking\InitCmsBundle\Entity\Page,
    Networking\InitCmsBundle\Helper\PageHelper,
    JMS\Serializer\EventDispatcher\EventSubscriberInterface,
    JMS\Serializer\EventDispatcher\Event;

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
                'format' => 'json',
                'class' => 'Networking\InitCmsBundle\Entity\Page'),
        );
    }


    /**
     * On Page Create
     *
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        $em = $args->getEntityManager();

        if ($entity instanceof Page) {

            if ($contentRoute = $entity->getContentRoute()) {

                $contentRoute->setObjectId($entity->getId());
                $contentRoute->setPath(PageHelper::getPageRoutePath($entity->getPath()));

                $em->persist($contentRoute);
                $em->flush();
            }
        }
    }

    /**
     * On Page Update
     *
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {

        /** @var $entity Page */
        $entity = $args->getEntity();

        $em = $args->getEntityManager();

        if ($entity instanceof Page) {
            $contentRoute = $entity->getContentRoute();
            $contentRoute->setPath(PageHelper::getPageRoutePath($entity->getPath()));

            foreach ($entity->getAllChildren() as $child) {
                $contentRoute = $child->getContentRoute();
                $contentRoute->setPath(PageHelper::getPageRoutePath($child->getPath()));

                if ($entity->getStatus() == Page::STATUS_PUBLISHED) {
                    if ($childSnapshot = $child->getSnapshot()) {
                        $snapshotRoute = $childSnapshot->getContentRoute();
                        $snapShot = json_decode($childSnapshot->getVersionedData());

                        // we want to find and replace
                        $snapshotPath = $snapShot->path;

                        //entity id can be used to locate slug in snapshot path
                        $entityId = $entity->getId();

                        // thing to replace
                        $entitySlug = $entity->getUrl();

                        $newPath = PageHelper::replaceSlugInPath($snapshotPath, $entityId, $entitySlug);

                        $snapshotRoute->setPath(PageHelper::getPageRoutePath($newPath));

                        $em->persist($snapshotRoute);
                    }
                }

                $em->persist($child);
            }

            $em->flush();
        }
    }

    /**
     * @param \JMS\Serializer\EventDispatcher\Event $event
     */
    public function onPostDeserialize(Event $event)
    {
        /** @var $page Page */
        $page = $event->getObject();

        $doctrine = $this->container->get('doctrine');


        $er = $doctrine->getRepository('NetworkingInitCmsBundle:Page');

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
    }
}
