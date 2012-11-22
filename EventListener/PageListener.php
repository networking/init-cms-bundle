<?php

namespace Networking\InitCmsBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;

use Networking\InitCmsBundle\Entity\ContentRoute;
use Networking\InitCmsBundle\Entity\Page;

class PageListener
{
    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        /** @var $entity Page */
        $entity = $args->getEntity();
        $em = $args->getEntityManager();

        if ($entity instanceof Page) {

            $contentRoute = $entity->getContentRoute();
            $contentRoute->setPath($this->getPageRoutePath($entity->getPath()));

            foreach ($entity->getAllChildren() as $child) {
                $contentRoute = $child->getContentRoute();
                $contentRoute->setPath($this->getPageRoutePath($child->getPath()));
                $em->persist($child);
            }

            $em->flush();
        }
    }

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();

        if ($entity instanceof Page) {

            if ($contentRoute = $entity->getContentRoute()) {

                $contentRoute->setObjectId($entity->getId());
                $contentRoute->setPath($this->getPageRoutePath($entity->getPath()));

                $em->persist($contentRoute);
                $em->flush();
            }

        }
    }

    /**
     * @param $path
     * @return string
     */
    public function getPageRoutePath($path)
    {
        $pathArray = explode(Page::PATH_SEPARATOR, $path);

        foreach ($pathArray as $key => $path) {
            $pathArray[$key] = preg_replace('/-(\d)+$/', '', $path);
        }
        $path = implode(Page::PATH_SEPARATOR, $pathArray);

        if (substr($path, 0, 1) != Page::PATH_SEPARATOR) {
            $path = Page::PATH_SEPARATOR . $path;
        }

        return $path;
    }
}
