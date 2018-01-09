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

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Gaufrette\Util;
use Sonata\MediaBundle\Entity\BaseMedia as BaseMedia;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Media
 * @package Networking\InitCmsBundle\Entity
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class Media extends BaseMedia
{

    /**
     * @var integer $id
     */
    protected $id;

    /**
     * @var ArrayCollection $tags
     */
    protected $tags;

    /**
     * @var string $locale
     */
    protected $locale;

    /**
     * @var ArrayCollection $galleryHasMedias
     */
    protected $galleryHasMedias;

    /**
     * @var string
     */
    protected $md5File;

    /**
     *
     */
    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }

    /**
     * {@inheritdoc}
     */
    public function setBinaryContent($binaryContent)
    {
        $this->previousProviderReference = $this->providerReference;
        $this->providerReference = null;
        $this->binaryContent = $binaryContent;
        $checksum = $this->getChecksum();
        $this->setMd5File($checksum);
    }

    /**
     * Add tags
     *
     * @param \Networking\InitCmsBundle\Entity\Tag $tag
     * @return $this
     */
    public function addTags(Tag $tag)
    {
        $this->tags->add($tag);

        return $this;
    }

    /**
     * Get tags
     *
     * @return ArrayCollection
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * @param  \Doctrine\Common\Collections\ArrayCollection $tags
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
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Getter Used in Form(field:networking_type_mediaprint)
     *
     * @return integer $id
     */
    public function getImage()
    {
        return $this->getId();
    }

    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * fake setter
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
}