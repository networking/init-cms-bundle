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

use Doctrine\ORM\EntityNotFoundException,
    Doctrine\ORM\Event\LifecycleEventArgs,
    JMS\Serializer\Serializer,
    Networking\InitCmsBundle\Entity\BasePage as Page,
    Networking\InitCmsBundle\Entity\LayoutBlock;

/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class LayoutBlockListener
{

    /**
     * @var \JMS\Serializer\Serializer $serializer
     */
    protected $serializer;

    /**
     * @param \JMS\Serializer\Serializer $serializer
     */
    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $layoutBlock = $args->getEntity();
        if ($layoutBlock instanceof LayoutBlock) {
            if ($contentObject = $layoutBlock->getSnapshotContent()) {
                $contentObject = $this->serializer->deserialize($contentObject, $layoutBlock->getClassType(), 'json');
                $em = $args->getEntityManager();

                try {
                    $em->persist($contentObject);
                    $contentObject = $em->merge($contentObject);
                } catch (EntityNotFoundException $e) {
                    $em->detach($contentObject);
                    $classType = $layoutBlock->getClassType();
                    $contentObject = new $classType;
                    $em->persist($contentObject);
                }

                $em->flush();

                $layoutBlock->setObjectId($contentObject->getId());

                $em->persist($layoutBlock);
                $em->flush();

            } else {
                $this->autoPageDraft($args);
            }
        }
    }

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $layoutBlock = $args->getEntity();
        if ($layoutBlock instanceof LayoutBlock) {


            if (!$layoutBlock->getSnapshotContent()) {
                $this->autoPageDraft($args);
            }
        }
    }

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();
        if ($entity instanceof LayoutBlock) {

            $classType = $entity->getClassType();
            $repo = $em->getRepository($classType);
            $object = $repo->find($entity->getObjectId());
            if ($object) {
                $em->remove($object);
            }

            if (!$entity->getSnapshotContent() && !$entity->getSetNoAutoDraft()) {
                $this->autoPageDraft($args);
            }
        }

//        if ($entity instanceof Gallery) {
//
//            $repo = $em->getRepository('NetworkingInitCmsBundle:GalleryView');
//            $galleryViews = $repo->findBy(array('mediaGallery' => $entity->getId()));
//
//            foreach ($galleryViews as $galleryView) {
//                $layoutBlock = $galleryView->getLayoutBlock();
//                $em->remove($layoutBlock);
//            }
//        }
    }

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function autoPageDraft(LifecycleEventArgs $args)
    {
        $layoutBlock = $args->getEntity();
        if ($layoutBlock instanceof LayoutBlock) {

            $page = $layoutBlock->getPage();
            $page->setStatus(Page::STATUS_DRAFT);
            $em = $args->getEntityManager();
            $em->persist($page);
        }
    }
}
