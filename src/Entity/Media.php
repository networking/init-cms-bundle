<?php

declare(strict_types=1);


/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gaufrette\Util;
use Networking\InitCmsBundle\Model\IgnoreRevertInterface;
use Sonata\MediaBundle\Entity\BaseMedia as BaseMedia;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Serializer\Annotation\Ignore;

/**
 * Class Media.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
#[ORM\Entity]
#[ORM\Table(name: 'media__media')]
#[ORM\HasLifecycleCallbacks]
class Media extends BaseMedia implements IgnoreRevertInterface
{
    
    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected $id;

    /**
     * @var ArrayCollection
     */
    #[ORM\ManyToMany(targetEntity: 'Networking\InitCmsBundle\Entity\Tag')]
    #[ORM\JoinTable( name: 'media_tags')]
    #[ORM\JoinColumn(name: 'media_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    #[ORM\InverseJoinColumn(name: 'tag_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    protected $tags;

    /**
     * @var string
     */
    #[ORM\Column(name: 'locale', type: 'string', length: 6, nullable: true)]
    protected $locale;

    /**
     * @var string
     */
    #[ORM\Column(name: 'md5_file', type: 'string', length: 255, nullable: true)]
    protected $md5File;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
        $this->galleryItems = new ArrayCollection();
    }


    /**
     * {@inheritdoc}
     */
    #[Ignore]
    public function setBinaryContent($binaryContent): void
    {

        $this->previousProviderReference = $this->providerReference;
        $this->providerReference = null;
        $this->binaryContent = $binaryContent;
        $checksum = $this->getChecksum();
        $this->setMd5File($checksum);
    }

    /**
     * Add tags.
     *
     *
     * @return $this
     */
    public function addTags(Tag $tag)
    {
        $this->tags->add($tag);

        return $this;
    }

    /**
     * Get tags.
     *
     * @return ArrayCollection
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param ArrayCollection|null $tags
     *
     * @return $this
     */
    public function setTags(ArrayCollection $tags = null)
    {
        $this->tags = $tags;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * @param string $locale
     *
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Getter Used in Form(field:networking_type_mediaprint).
     *
     * @return int $id
     */
    public function getImage()
    {
        return $this->getId();
    }

    /**
     * Get id.
     *
     * @return int $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * fake setter.
     *
     * @return $this
     */
    public function setImage($image)
    {
        return $this;
    }

    /**
     * @return string
     */
    public function getMd5File()
    {
        return $this->md5File;
    }

    /**
     * @param string $md5File
     */
    public function setMd5File($md5File)
    {
        $this->md5File = $md5File;
    }

    public function getChecksum()
    {
        if ($this->getBinaryContent() instanceof UploadedFile) {
            return Util\Checksum::fromFile($this->getBinaryContent()->getPathName());
        }

        return Util\Checksum::fromContent($this->getBinaryContent());
    }

    /**
     * @return $this
     */
    public function getSelf()
    {
        return $this;
    }

    #[ORM\PrePersist]
    public function prePersist(): void
    {
        $checksum = $this->getChecksum();
        $this->setMd5File($checksum);

        parent::prePersist();
    }

    #[ORM\PreUpdate]
    public function preUpdate(): void
    {
        $checksum = $this->getChecksum();
        $this->setMd5File($checksum);

       parent::preUpdate();
    }
}
