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
use Gaufrette\Util;
use Networking\InitCmsBundle\Model\IgnoreRevertInterface;
use Sonata\MediaBundle\Entity\BaseMedia as BaseMedia;
use Symfony\Component\HttpFoundation\File\UploadedFile;

/**
 * Class Media.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class Media extends BaseMedia implements IgnoreRevertInterface
{
    
    /**
     * @var int
     */
    protected $id;

    /**
     * @var ArrayCollection
     */
    protected $tags;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var ArrayCollection
     */
    protected $galleryHasMedias;

    /**
     * @var string
     */
    protected $md5File;

    public function __construct()
    {
        $this->tags = new ArrayCollection();
    }


    /**
     * Add tags.
     *
     * @param \Networking\InitCmsBundle\Entity\Tag $tag
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

    public function prePersist()
    {
        $checksum = $this->getChecksum();
        $this->setMd5File($checksum);

        return parent::prePersist();
    }

    public function preUpdate()
    {
        $checksum = $this->getChecksum();
        $this->setMd5File($checksum);

        return parent::preUpdate();
    }
}
