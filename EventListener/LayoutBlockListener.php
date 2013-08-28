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
    JMS\Serializer\Serializer,
    Networking\InitCmsBundle\Entity\Page,
    Networking\InitCmsBundle\Entity\LayoutBlock;
use Networking\InitCmsBundle\Entity\Gallery;
use Networking\InitCmsBundle\Entity\GalleryView;

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

                if ($contentObject instanceof \Networking\InitCmsBundle\Entity\GalleryView) {
                    $er = $args->getEntityManager()->getRepository('Networking\InitCmsBundle\Entity\GalleryView');
                    $gallery = $er->find($contentObject->getMediaGallery()->getId());
                    $contentObject->setMediaGallery($gallery);
                }

                $em = $args->getEntityManager();
                $contentObject->setLayoutBlock($layoutBlock);
                $em->persist($contentObject);
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

        if ($entity instanceof LayoutBlock) {
            if (!$entity->getSnapshotContent() && !$entity->getIsSnapshot()) {
                $this->autoPageDraft($args);
            }
        }

        if ($entity instanceof Gallery) {
            $em = $args->getEntityManager();
            $repo = $em->getRepository('NetworkingInitCmsBundle:GalleryView');
            $galleryViews = $repo->findBy(array('mediaGallery' => $entity->getId()));

            foreach ($galleryViews as $galleryView) {
                $layoutBlock = $galleryView->getLayoutBlock();
                $em->remove($layoutBlock);
            }
        }
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
