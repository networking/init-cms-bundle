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


/**
 * HelpText
 *
 */
interface HelpTextInterface
{

    /**
     * @return string
     */
    public function __toString();

    /**
     * Set locale
     *
     * @param string $locale
     * @return $this
     */
    public function setLocale($locale);

    /**
     * Get locale
     *
     * @return string
     */
    public function getLocale();

    /**
     * Set translationKey
     *
     * @param string $translationKey
     * @return $this
     */
    public function setTranslationKey($translationKey);

    /**
     * Get translationKey
     *
     * @return string
     */
    public function getTranslationKey();

    /**
     * Set text
     *
     * @param string $text
     * @return $this
     */
    public function setText($text);

    /**
     * Get text
     *
     * @return string
     */
    public function getText();

    /**
     * Set translationDomain
     *
     * @param string $translationDomain
     * @return $this
     */
    public function setTitle($translationDomain);

    /**
     * Get translationDomain
     *
     * @return string
     */
    public function getTitle();


    /**
     * Set isDeletable
     *
     * @param string $isDeletable
     * @return $this
     */
    public function setIsDeletable($isDeletable);

    /**
     * Get isDeletable
     *
     * @return string
     */
    public function getIsDeletable();

    /**
     * @return string
     */
    public function isDeletable();
}
