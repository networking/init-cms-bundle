<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Admin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Ibrows\Bundle\SonataAdminAnnotationBundle\Reader\SonataAdminAnnotationReaderInterface;
use Symfony\Component\Intl\Intl;

/**
 * Class BaseAdmin
 * @package Networking\InitCmsBundle\Admin
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class BaseAdmin extends Admin
{
    /**
     * @var array $languages
     */
    protected $languages;

    /**
     * @var Array $trackedActions
     */
    protected $trackedActions = array('list', 'edit');

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
     * Set up listner to make sure the correct locale is used
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
     * example: array('de_CH' => 'Deutsch', 'en_GB' => 'English')
     *
     * @return array
     */
    protected function getLocaleChoices()
    {
        $localeChoices = array();

        if (!$this->request) {
            return array();
        }

        if (!$this->getRequest()->get('locale')) {
            $locale = $this->getRequest()->getLocale();
        } else {
            $locale = $this->getRequest()->get('locale');
        }

        if (is_array($locale) && array_key_exists('value', $locale)) {
            $locale = $locale['value'];
        }

        $localeList = Intl::getLocaleBundle()->getLocaleNames(substr($locale, 0, 2));

        foreach ($this->languages as $language) {
            $localeChoices[$language['locale']] = $localeList[$language['locale']];
        }

        return $localeChoices;
    }

    /**
     * @return int|string
     */
    public function getDefaultLocale()
    {
        if (!$this->request) {
            return '';
        }

        if (!$this->getRequest()->get('locale')) {
            $locale = $this->getRequest()->getLocale();
        } else {
            $locale = $this->getRequest()->get('locale');
        }
        //if the locale is posted in the filter
        if (is_array($locale)) {
            if (array_key_exists('value', $locale)) {
                $locale = $locale['value'];
            }
        }

        if (!array_key_exists($locale, $this->getLocaleChoices())) {
            if (strlen($locale) > 2) {
                $shortLocale = substr($locale, 0, 2);
            } else {
                $shortLocale = $locale;
            }

            foreach ($this->getLocaleChoices() as $key => $locale) {
                if (strpos($key, $shortLocale) !== false) {
                    return $key;
                }
            }


            foreach ($this->getLocaleChoices() as $key => $locale) {
                if (strpos($key, $this->getContainer()->getParameter('locale')) !== false) {
                    return $key;
                }
            }

        }

        return $locale;

    }

    /**
     * @param $trackedActions
     * @return BaseAdmin
     */
    public function setTrackedActions($trackedActions)
    {
        $this->trackedActions = $trackedActions;

        return $this;
    }

    /**
     * @return Array
     */
    public function getTrackedActions()
    {
        return $this->trackedActions;
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $this->getSonataAnnotationReader()->configureListFields($this->getClass(), $listMapper);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $this->getSonataAnnotationReader()->configureFormFields($this->getClass(), $formMapper);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureShowFields(ShowMapper $showMapper)
    {
        $this->getSonataAnnotationReader()->configureShowFields($this->getClass(), $showMapper);
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $this->getSonataAnnotationReader()->configureDatagridFilters($this->getClass(), $datagridMapper);
    }

    /**
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->getConfigurationPool()->getContainer();
    }

    /**
     * @return SonataAdminAnnotationReaderInterface
     */
    protected function getSonataAnnotationReader()
    {
        return $this->getContainer()->get('ibrows_sonataadmin.annotation.reader');
    }
}
