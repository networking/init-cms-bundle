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

namespace Networking\InitCmsBundle\Admin;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Networking\InitCmsBundle\Reader\SonataAdminAnnotationReaderInterface;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Intl\Locales;

/**
 * Class BaseAdmin.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class BaseAdmin extends AbstractAdmin implements ContainerAwareInterface
{

    protected $annotationReader;

    /**
     * @var array
     */
    protected $languages;

    /**
     * @var array
     */
    protected $trackedActions = [];

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Sets the container.
     */
    public function setContainer(ContainerInterface $container = null){

        $this->container = $container;
    }


    /**
     * Set the language paramenter to contain a list of languages most likely
     * passed from the config.yaml file.
     *
     * @param array $languages
     */
    public function setLanguages(array $languages)
    {
        $this->languages = $languages;
    }

    /**
     * Set up listner to make sure the correct locale is used.
     */
    public function setUpTranslatableLocale()
    {
        /** @var \Gedmo\Translatable\TranslatableListener $translatable */
        $translatableListener = $this->getContainer()->get('stof_doctrine_extensions.listener.translatable', ContainerInterface::NULL_ON_INVALID_REFERENCE);
        if ($translatableListener) {
            $translatableListener->setTranslatableLocale($this->getDefaultLocale());
        }
    }

    /**
     * Provide an array of locales where the locale is the key and the label is
     * the value for easy display in a dropdown select for example
     * example: array('de_CH' => 'Deutsch', 'en_GB' => 'English').
     *
     * @return array
     */
    protected function getLocaleChoices()
    {
        $localeChoices = [];

        if (!$this->getRequest()) {
            return [];
        }
        $locale = $this->getRequest()->getLocale();
        
        $localeList = Locales::getNames(substr($locale, 0, 2));
        foreach ($this->languages as $language) {
            $localeChoices[$localeList[$language['locale']]] = $language['locale'];
        }

        return $localeChoices;
    }

    /**
     * @return string
     */
    public function getDefaultLocale()
    {
        if (!$this->getRequest()) {
            return '';
        }

        $locale = $this->getRequest()->get('locale');

        if (!$locale) {
            $locale = $this->getRequest()->getLocale();
        }

        //if the locale is posted in the filter
        if (is_array($locale)) {
            if (array_key_exists('value', $locale)) {
                $locale = $locale['value'];
            }
        }

        $localeChoices = array_flip($this->getLocaleChoices());


        if (!array_key_exists($locale, $localeChoices)) {
            foreach ($localeChoices as $key => $choice) {
                if (strpos($key, substr($locale, 0, 2)) !== false) {
                    return $locale;
                }
            }

            return $this->languages[0]['locale'];
        }



        return $locale;
    }

    /**
     * @param array $trackedActions
     *
     * @return BaseAdmin
     */
    public function setTrackedActions(array $trackedActions)
    {
        $this->trackedActions = $trackedActions;

        return $this;
    }

    /**
     * @return array
     */
    public function getTrackedActions()
    {
        return $this->trackedActions;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper): void
    {
        $this->getSonataAnnotationReader()->configureListFields($this->getClass(), $listMapper);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper): void
    {
        $this->getSonataAnnotationReader()->configureFormFields($this->getClass(), $formMapper);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $showMapper): void
    {
        $this->getSonataAnnotationReader()->configureShowFields($this->getClass(), $showMapper);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper): void
    {
        $this->getSonataAnnotationReader()->configureDatagridFilters($this->getClass(), $datagridMapper);
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->container;
    }

    /**
     * @return SonataAdminAnnotationReaderInterface
     */
    protected function getSonataAnnotationReader()
    {
        return $this->annotationReader;
    }

    /**
     * @param SonataAdminAnnotationReaderInterface $annotationReader
     * @return $this
     */
    public function setSonataAnnotationReader(SonataAdminAnnotationReaderInterface $annotationReader)
    {
        $this->annotationReader = $annotationReader;
        return $this;
    }
}
