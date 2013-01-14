<?php

/*
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @author net working AG <info@networking.ch>
 */
abstract class BaseAdmin extends Admin implements ContainerAwareInterface
{
    /**
     * @var array $languages
     */
    protected $languages;

    /**
     * @var ContainerInterface $container
     */
    protected $container;

    /**
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * Set the language paramenter to contain a list of languages most likely
     * passed from the config.yml file
     *
     * @param array $languages
     */
    public function setLanguages(array $languages)
    {
        $this->languages = $languages;
    }

    /**
     * Provide an array of locales where the locale is the key and the label is
     * the value for easy display in a dropdown select for example
     * example: array('de_CH' => 'Deutsch', 'en_GB' => 'English')
     *
     * @return array
     */
    protected function getLocaleChoices()
    {
        $locale = array();

        foreach ($this->languages as $language) {
            $locale[$language['locale']] = $language['label'];
        }
        return $locale;
    }

    /**
     * @return int|string
     */
    public function getDefaultLocale()
    {

        if (!array_key_exists($this->getRequest()->getLocale(), $this->getLocaleChoices())) {
            $shortLocale = substr($this->getRequest()->getLocale(), 0, 2);

            foreach ($this->getLocaleChoices() as $key => $locale) {
                if (strpos($key, $shortLocale) !== false) {
                    return $key;
                }
            }



            foreach ($this->getLocaleChoices() as $key => $locale) {
                if (strpos($key, $this->container->getParameter('locale')) !== false) {
                    return $key;
                }
            }

        } else {
            return $this->getRequest()->getLocale();
        }

    }
}
