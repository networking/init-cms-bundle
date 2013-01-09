<?php

namespace Networking\InitCmsBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs,
    Symfony\Component\HttpFoundation\Session\Session,
    Networking\InitCmsBundle\Entity\Page,
    Networking\InitCmsBundle\Helper\PageHelper;

class PageListener
{


    /**
     * @var Session $session
     */
    protected $session;

    public function __construct(Session $session){
        $this->session = $session;
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
                $em->persist($child);
            }

            $em->flush();
        }
    }
}
