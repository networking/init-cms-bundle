<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Networking\InitCmsBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Networking\InitCmsBundle\Entity\Gallery as MediaGallery;
use Networking\InitCmsBundle\Model\GalleryViewInterface;
use Networking\InitCmsBundle\Annotation as Sonata;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Class GalleryView.
 */
#[ORM\Entity]
#[ORM\Table(name: 'gallery_view')]
class GalleryView extends LayoutBlock implements GalleryViewInterface
{
    /**
     * @var int
     *
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected $id;

    #[ORM\ManyToOne(
        targetEntity: MediaGallery::class,
        cascade: ["merge"],
        fetch: 'LAZY')]
    #[ORM\JoinColumn(name: 'media_gallery_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[Sonata\FormMapper(
        name: 'mediaGallery',
        type: 'Symfony\Bridge\Doctrine\Form\Type\EntityType',
        options: [
            'label' => 'form.label_gallery',
            'data_class' => null,
            'class' => 'Networking\InitCmsBundle\Entity\Gallery',
            'layout' => 'horizontal',
            'translation_domain' => 'messages',
            'required' => false,
        ]
    )]
    #[Assert\NotNull]
    protected ?Gallery $mediaGallery = null;


    #[ORM\Column(type: 'string', length: 50)]
    #[Sonata\FormMapper(
        name: 'galleryType',
        type: 'Symfony\Component\Form\Extension\Core\Type\ChoiceType',
        options: [
            'label' => 'form.label_gallery_type',
            'choices' => ['list' => 'list', 'lightbox' => 'lightbox', 'carousel' => 'carousel'],
            'translation_domain' => 'messages',
            'layout' => 'horizontal',
        ]
    )]
    protected string $galleryType = 'lightbox';



    public function __clone(): void
    {
        $this->id = null;
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
    public function getTemplateOptions($params = []): array
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
    public function getAdminContent(): array
    {
        return [
            'content' => ['galleryView' => $this],
            'template' => '@NetworkingInitCms/GalleryAdmin/gallery_view_block.html.twig',
        ];
    }

    public function hasMedia(): bool
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

    public function getContentTypeName(): string
    {
        return 'Gallery Viewer';
    }
}
