<?php

namespace Networking\InitCmsBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs,
    Symfony\Component\HttpFoundation\Session\Session,
    JMS\SerializerBundle\Serializer\SerializerInterface,
    Networking\InitCmsBundle\Entity\ContentRoute,
    Networking\InitCmsBundle\Entity\Page,
    Networking\InitCmsBundle\Entity\PageSnapshot;

class PageListener
{

    /**
     * @var SerializerInterface $serializer
     */
    protected $serializer;

    /**
     * @var Session $session
     */
    protected $session;

    public function __construct(Session $session){
        $this->session = $session;
    }

    public function setSerializer(SerializerInterface $serializer)
    {
        $this->serializer = $serializer;
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
                $contentRoute->setPath($this->getPageRoutePath($entity->getPath()));

                $em->persist($contentRoute);
                $em->flush();
            }

            if ($entity->getStatus() == Page::STATUS_PUBLISHED) {

                $this->makeSnapshot($entity, $em);
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
            $contentRoute->setPath(self::getPageRoutePath($entity->getPath()));

            foreach ($entity->getAllChildren() as $child) {
                $contentRoute = $child->getContentRoute();
                $contentRoute->setPath($this->getPageRoutePath($child->getPath()));
                $em->persist($child);
            }

            $em->flush();

            if ($entity->getStatus() == Page::STATUS_PUBLISHED) {

                $this->makeSnapshot($entity, $em);
            }
        }
    }

    /**
     * @param $path
     * @return string
     */
    public static function getPageRoutePath($path)
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


    public function makeSnapshot(Page $page, \Doctrine\ORM\EntityManager $em)
    {
        $pageSnapshot = new PageSnapshot($page);

        foreach ($page->getLayoutBlock() as $layoutBlock) {
            /** @var $layoutBlock \Networking\InitCmsBundle\Entity\LayoutBlock  */
            $layoutBlockContent = $em->getRepository($layoutBlock->getClassType())->find($layoutBlock->getObjectId());
            $layoutBlock->takeSnapshot($this->serializer->serialize($layoutBlockContent, 'json'));
        }

        $pageSnapshot->setVersionedData($this->serializer->serialize($page, 'json'))
                     ->setPage($page);

        if ($oldPageSnapshot = $page->getSnapshot()) {
            $snapshotContentRoute = $oldPageSnapshot->getContentRoute();
        } else {
            $snapshotContentRoute = new ContentRoute();
        }

        $pageSnapshot->setContentRoute($snapshotContentRoute);

        $em->persist($pageSnapshot);
        $em->flush();

        $snapshotContentRoute->setPath(self::getPageRoutePath($page->getPath()));
        $snapshotContentRoute->setObjectId($pageSnapshot->getId());

        $em->persist($snapshotContentRoute);
        $em->flush();
    }
}
