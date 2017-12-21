<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * HelpText
 *
 * @ORM\Table(name="help_text")
 * @ORM\Entity(repositoryClass="Networking\InitCmsBundle\Entity\HelpTextRepository")
 */
class HelpText implements HelpTextInterface
{
    /**
     * @var integer
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     *
     * @ORM\Column(name="locale", type="string", length=6)
     */
    protected $locale;

    /**
     * @var string
     *
     * @ORM\Column(name="translation_key", type="string", length=255)
     */
    protected $translationKey;

    /**
     * @var string
     *
     * @ORM\Column(name="text", type="text")
     */
    protected $text;

    /**
     * @var string
     *
     * @ORM\Column(name="title", type="string", length=255, nullable=true)
     */
    protected $title;

    /**
     * @var string $isDeletable
     *
     * @ORM\Column(name="is_deletable", type="boolean")
     */
    protected $isDeletable = true;

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->title;
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set locale
     *
     * @param string $locale
     * @return $this
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;

        return $this;
    }

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set translationKey
     *
     * @param string $translationKey
     * @return $this
     */
    public function setTranslationKey($translationKey)
    {
        $this->translationKey = $translationKey;

        return $this;
    }

    /**
     * Get translationKey
     *
     * @return string
     */
    public function getTranslationKey()
    {
        return $this->translationKey;
    }

    /**
     * Set text
     *
     * @param string $text
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set translationDomain
     *
     * @param string $translationDomain
     * @return $this
     */
    public function setTitle($translationDomain)
    {
        $this->title = $translationDomain;

        return $this;
    }

    /**
     * Get translationDomain
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }


    /**
     * Set isDeletable
     *
     * @param string $isDeletable
     * @return $this
     */
    public function setIsDeletable($isDeletable)
    {
        $this->isDeletable = $isDeletable;

        return $this;
    }

    /**
     * Get isDeletable
     *
     * @return string
     */
    public function getIsDeletable()
    {
        return $this->isDeletable;
    }

    /**
     * @return string
     */
    public function isDeletable()
    {
        return $this->getIsDeletable();
    }
}
