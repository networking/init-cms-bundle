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

use Doctrine\ORM\Mapping as ORM;
use Networking\InitCmsBundle\Model\HelpTextInterface;

/**
 * HelpText.
 */
#[ORM\Entity]
#[ORM\Table(name: 'help_text')]
class HelpText implements HelpTextInterface, \Stringable
{
    /**
     * @var int
     *
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected $id;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 6)]
    protected $locale;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255)]
    protected $translationKey;

    /**
     * @var string
     */
    #[ORM\Column(type: 'text')]
    protected $text;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    protected $title;

    /**
     * @var string
     */
    #[ORM\Column(type: 'boolean')]
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
