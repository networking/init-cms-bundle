<?php

namespace Networking\InitCmsBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs,
    Symfony\Component\HttpFoundation\Session\Session,
    Symfony\Component\DependencyInjection\ContainerInterface,
    Networking\InitCmsBundle\Entity\Page,
    Networking\InitCmsBundle\Helper\PageHelper,
    JMS\SerializerBundle\Serializer\EventDispatcher\EventSubscriberInterface,
    JMS\SerializerBundle\Serializer\EventDispatcher\Event;

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

    public function __construct(Session $session, ContainerInterface $container)
    {
        $this->session = $session;
        $this->container = $container;
    }

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
                        $entitySlug = $entity->getSlug();

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

    public function onPostDeserialize(Event $event)
    {
        /** @var $page Page */
        $page = $event->getObject();

        $doctrine = $this->container->get('doctrine');


        if ($parent = $page->getParent()) {
            $er = $doctrine->getRepository('NetworkingInitCmsBundle:Page');
            $parent = $er->find($page->getParent());
            $page->setParent($parent);
        } else {
            $page->setParent(null);
        }

        if ($parents = $page->getParents()) {
            $er = $doctrine->getRepository('NetworkingInitCmsBundle:Page');
            foreach ($parents as $key => $parent) {
                $parents[$key] = $er->find($parent);
            }

            $page->setParents($parents);
        }

        if ($children = $page->getChildren()) {
            $er = $doctrine->getRepository('NetworkingInitCmsBundle:Page');
            foreach ($children as $key => $child) {
                $children[$key] = $er->find($child);
            }

            $page->setChildren($children);
        }

        if ($originals = $page->getOriginals()) {
            $er = $doctrine->getRepository('NetworkingInitCmsBundle:Page');
            foreach ($originals as $key => $original) {
                $originals[$key] = $er->find($original);
            }

            $page->setOriginals($originals);
        }

        if ($translations = $page->getTranslations()) {
            $er = $doctrine->getRepository('NetworkingInitCmsBundle:Page');
            foreach ($translations as $key => $translation) {
                $translations[$key] = $er->find($translation);
            }

            $page->setOriginals($translations);
        }
    }
}
