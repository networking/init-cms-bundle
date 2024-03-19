<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Networking\InitCmsBundle\Admin;

use Gedmo\Translatable\TranslatableListener;
use Networking\InitCmsBundle\AttributeReader\SonataAdminAttributeReaderInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Networking\InitCmsBundle\Reader\SonataAdminAnnotationReaderInterface;
use Symfony\Component\Intl\Intl;
use Symfony\Component\Intl\Locales;

/**
 * Class BaseAdmin.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class BaseAdmin extends AbstractAdmin
{

    protected $attributeReader;

    /**
     * @var array
     */
    protected $languages;

    /**
     * @var array
     */
    protected $trackedActions = [];


    /**
     * Set the language paramenter to contain a list of languages most likely
     * passed from the config.yaml file.
     *
     * @param array $languages
     */
    public function setLanguages(array $languages): void
    {
        $this->languages = $languages;
    }


    /**
     * Provide an array of locales where the locale is the key and the label is
     * the value for easy display in a dropdown select for example
     * example: array('de_CH' => 'Deutsch', 'en_GB' => 'English').
     *
     * @return array
     */
    protected function getLocaleChoices(): array
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
    public function getDefaultLocale(): string
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
    public function setTrackedActions(array $trackedActions): AbstractAdmin
    {
        $this->trackedActions = $trackedActions;

        return $this;
    }

    /**
     * @return array
     */
    public function getTrackedActions(): array
    {
        return $this->trackedActions;
    }

    protected function configureListFields(ListMapper $list): void
    {
        $this->getSonataAnnotationReader()?->configureListFields($this->getClass(), $list);
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $this->getSonataAnnotationReader()?->configureFormFields($this->getClass(), $form);
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $this->getSonataAnnotationReader()?->configureShowFields($this->getClass(), $show);
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $this->getSonataAnnotationReader()?->configureDatagridFilters($this->getClass(), $filter);
    }

    protected function getSonataAnnotationReader(): ?SonataAdminAttributeReaderInterface
    {
        return $this->attributeReader;
    }

    /**
     * @return $this
     */
    public function setSonataAnnotationReader(SonataAdminAttributeReaderInterface $annotationReader): self
    {
        $this->attributeReader = $annotationReader;

        return $this;
    }
}
