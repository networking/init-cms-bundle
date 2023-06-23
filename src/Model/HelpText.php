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
namespace Networking\InitCmsBundle\Model;

use Doctrine\ORM\Mapping as ORM;

/**
 * HelpText.
 *
 */
class HelpText implements HelpTextInterface, \Stringable
{
    /**
     * @var int
     *
     */
    protected $id;

    /**
     * @var string
     */
    protected $locale;

    /**
     * @var string
     */
    protected $translationKey;

    /**
     * @var string
     */
    protected $text;

    /**
     * @var string
     */
    protected $title;

    /**
     * @var string
     */
    protected $isDeletable = true;

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->title;
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
     * Set locale.
     *
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
     * Get locale.
     *
     * @return string
     */
    public function getLocale()
    {
        return $this->locale;
    }

    /**
     * Set translationKey.
     *
     * @param string $translationKey
     *
     * @return $this
     */
    public function setTranslationKey($translationKey)
    {
        $this->translationKey = $translationKey;

        return $this;
    }

    /**
     * Get translationKey.
     *
     * @return string
     */
    public function getTranslationKey()
    {
        return $this->translationKey;
    }

    /**
     * Set text.
     *
     * @param string $text
     *
     * @return $this
     */
    public function setText($text)
    {
        $this->text = $text;

        return $this;
    }

    /**
     * Get text.
     *
     * @return string
     */
    public function getText()
    {
        return $this->text;
    }

    /**
     * Set translationDomain.
     *
     * @param string $translationDomain
     *
     * @return $this
     */
    public function setTitle($translationDomain)
    {
        $this->title = $translationDomain;

        return $this;
    }

    /**
     * Get translationDomain.
     *
     * @return string
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * Set isDeletable.
     *
     * @param string $isDeletable
     *
     * @return $this
     */
    public function setIsDeletable($isDeletable)
    {
        $this->isDeletable = $isDeletable;

        return $this;
    }

    /**
     * Get isDeletable.
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
