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

use Networking\InitCmsBundle\Reader\SonataAdminAnnotationReaderInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Intl\Locales;

/**
 * Class BaseAdmin.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class BaseAdmin extends AbstractAdmin
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

    protected function configureDefaultSortValues(array &$sortValues): void
    {
        $sortValues[DatagridInterface::PER_PAGE] = 1000000;
    }

    /**
     * Set the language paramenter to contain a list of languages most likely
     * passed from the config.yaml file.
     */
    public function setLanguages(array $languages): void
    {
        $this->languages = $languages;
    }

    /**
     * Set up listner to make sure the correct locale is used.
     */
    public function setUpTranslatableLocale(): void
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

    public function getDefaultLocale(): string
    {
        if (!$this->hasRequest()) {
            return '';
        }

        $locale = $this->getRequest()->get('locale');

        if (!$locale) {
            $locale = $this->getRequest()->getLocale();
        }

        // if the locale is posted in the filter
        if (is_array($locale)) {
            if (array_key_exists('value', $locale)) {
                $locale = $locale['value'];
            }
        }

        $localeChoices = array_flip($this->getLocaleChoices());

        if (!array_key_exists($locale, $localeChoices)) {
            foreach ($localeChoices as $key => $choice) {
                if (false !== strpos($key, substr($locale, 0, 2))) {
                    return $key;
                }
            }

            return $this->languages[0]['locale'];
        }

        return $locale;
    }

    /**
     * @return BaseAdmin
     */
    public function setTrackedActions(array $trackedActions): AbstractAdmin
    {
        $this->trackedActions = $trackedActions;

        return $this;
    }

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

    protected function getSonataAnnotationReader(): ?SonataAdminAnnotationReaderInterface
    {
        return $this->annotationReader;
    }

    /**
     * @return $this
     */
    public function setSonataAnnotationReader(SonataAdminAnnotationReaderInterface $annotationReader): self
    {
        $this->annotationReader = $annotationReader;

        return $this;
    }
}
