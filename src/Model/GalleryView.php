<?php

namespace Networking\InitCmsBundle\Model;

use Doctrine\ORM\Mapping as ORM;
use Networking\InitCmsBundle\Entity\Gallery as MediaGallery;
use Networking\InitCmsBundle\Annotation as Sonata;

/**
 * Networking\InitCmsBundle\Model\GalleryView.
 *
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="gallery_view")
 * @ORM\Entity()
 */
class GalleryView implements GalleryViewInterface
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var MediaGallery
     *
     * @ORM\ManyToOne(targetEntity="Networking\InitCmsBundle\Entity\Gallery", cascade={"merge"})
     * @ORM\JoinColumn( name="media_gallery_id", onDelete="CASCADE" )
     *
     * @Sonata\FormMapper(
     *      name="mediaGallery",
     *      type="Symfony\Bridge\Doctrine\Form\Type\EntityType",
     *      options={
     *          "label" = "form.label_gallery",
     *          "data_class"=null,
     *          "class"="Networking\InitCmsBundle\Entity\Gallery",
     *          "layout" = "horizontal",
     *      }
     * )
     */
    protected $mediaGallery;

    /**
     * @var string
     *
     * @ORM\Column(name="gallery_type", type="string", length=50)
     * @Sonata\FormMapper(
     *     name="galleryType",
     *     type="Symfony\Component\Form\Extension\Core\Type\ChoiceType",
     *     options={
     *          "label" = "form.label_gallery_type",
     *          "choices" = {"list" = "list", "lightbox" = "lightbox", "carousel" = "carousel"},
     *          "layout" = "horizontal",
     *     }
     * )
     */
    protected $galleryType = 'lightbox';

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;

    public function __clone()
    {
        $this->id = null;
    }

    public function prePersist()
    {
        $this->createdAt = $this->updatedAt = new \DateTime('now');
    }

    /**
     * Hook on pre-update operations.
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime('now');
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param \Networking\InitCmsBundle\Entity\Gallery $mediaGallery
     *
     * @return $this
     */
    public function setMediaGallery($mediaGallery)
    {
        $this->mediaGallery = $mediaGallery;

        return $this;
    }

    /**
     * @return \Networking\InitCmsBundle\Entity\Gallery
     */
    public function getMediaGallery()
    {
        return $this->mediaGallery;
    }

    /**
     * Set createdAt.
     *
     * @return $this
     */
    public function setCreatedAt()
    {
        $this->createdAt = new \DateTime();

        return $this;
    }

    /**
     * Get createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt.
     *
     * @param \DateTime $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt.
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param $galleryType
     *
     * @return $this
     *
     * @throws \InvalidArgumentException
     */
    public function setGalleryType($galleryType)
    {
        if (!in_array($galleryType, ['lightbox', 'carousel', 'list'])) {
            throw new \InvalidArgumentException('Gallery type not valid');
        }
        $this->galleryType = $galleryType;

        return $this;
    }

    /**
     * @return string
     */
    public function getGalleryType()
    {
        return $this->galleryType;
    }

    /**
     * @param array $params
     *
     * @return array
     */
    public function getTemplateOptions($params = [])
    {
        return [
            'mediaItems' => $this->getMediaGallery()->getGalleryItems(),
            'gallery' => $this->getMediaGallery(),
            'galleryView' => $this,
        ];
    }

    /**
     * @return array
     */
    public function getAdminContent()
    {
        return [
            'content' => ['galleryView' => $this],
            'template' => '@NetworkingInitCms/GalleryAdmin/gallery_view_block.html.twig',
        ];
    }

    /**
     * @return bool
     */
    public function hasMedia()
    {
        if (count($this->getMediaItems()) > 0) {
            return true;
        }

        return false;
    }

    /**
     * @return array
     */
    public function getMediaItems()
    {
        $mediaItems = $this->getMediaGallery() ? $this->getMediaGallery()->getGalleryItems() : [];

        return $mediaItems;
    }

    /**
     * @return string
     */
    public function getContentTypeName()
    {
        return 'Gallery Viewer';
    }
}
