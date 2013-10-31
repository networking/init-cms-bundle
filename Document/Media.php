<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Document;

use Sonata\MediaBundle\Document\BaseMedia;
use Doctrine\Common\Collections\ArrayCollection;

/**
 * Class Media
 * @package Networking\InitCmsBundle\Document
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
     *
     */
    public function __construct()
    {
        $this->tags = new ArrayCollection();
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
     * Add tags
     *
     * @param Tag  $tag
     * @return $this
     */
    public function addTags(Tag $tag)
    {
        $this->tags->add($tag);

        return $this;
    }

    /**
     * @param  \Doctrine\Common\Collections\ArrayCollection $tags
     * @return $this
     */
    public function setTags(ArrayCollection $tags)
    {
        $this->tags = $tags;

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
     * @param string $locale
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }
}