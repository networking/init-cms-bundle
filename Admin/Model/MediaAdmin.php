<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Admin\Model;

use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\MediaBundle\Form\DataTransformer\ProviderDataTransformer;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Admin\AdminInterface;
use Knp\Menu\ItemInterface as MenuItemInterface;
use Sonata\MediaBundle\Admin\BaseMediaAdmin as Admin;

/**
 * Class MediaAdmin
 * @package Networking\InitCmsBundle\Admin\Model
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class MediaAdmin extends Admin
{
    /**
     * @var string
     */
    protected $baseRoutePattern = 'cms/media';

    /**
     * @var array
     * @var array
     */
    protected $trackedActions = array('list');

    /**
     * @var array $languages
     */
    protected $languages;

    /**
     * @var array
     */
    protected $localisedMediaProviders = array('sonata.media.provider.file');


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
     * @param $trackedActions
     * @return $this
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
     * @return string
     */
    public function getIcon()
    {
        return 'glyphicon-picture';
    }

    /**
     * {@inheritdoc}
     */
    public function configureRoutes(RouteCollection $collection)
    {
        parent::configureRoutes($collection);
        $collection->add(
            'init_ckeditor_browser',
            'init_ckeditor_browser',
            array(
                '_controller' => 'NetworkingInitCmsBundle:CkeditorAdmin:browser'
            )
        );

        $collection->add(
            'init_ckeditor_upload_file',
            'init_ckeditor_upload_file',
            array(
                '_controller' => 'NetworkingInitCmsBundle:CkeditorAdmin:upload',
                'type' => 'file'
            )
        );

        $collection->add(
            'init_ckeditor_upload_image',
            'init_ckeditor_upload_image',
            array(
                '_controller' => 'NetworkingInitCmsBundle:CkeditorAdmin:upload',
                'type' => 'image'
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $media = $this->getSubject();


        if (!$media) {
            $media = $this->getNewInstance();
        }

        if (!$media || !$media->getProviderName()) {
            return;


        }

        $formMapper->getFormBuilder()->addModelTransformer(
            new ProviderDataTransformer($this->pool, $this->getClass()),
            true
        );
        $provider = $this->pool->getProvider($media->getProviderName());


        if ($media->getId()) {
            $provider->buildEditForm($formMapper);
        } else {
            $provider->buildCreateForm($formMapper);
        }

        if ($media->getId() && ($media->getProviderName() == 'sonata.media.provider.image' || $media->getProviderName(
                ) == 'sonata.media.provider.youtube')
        ) {

            $formMapper->remove('binaryContent', 'file');

            if ($media->getProviderName() == 'sonata.media.provider.youtube') {

                $formMapper->add(
                    'image',
                    'networking_type_mediaprint',
                    array('required' => false, 'label' => 'form.label_current_video')
                );

                $formMapper->add(
                    'binaryContent',
                    'text',
                    array('required' => false, 'label' => 'form.label_binary_content_youtube_new')
                );
            } else {
                $formMapper->add(
                    'image',
                    'networking_type_mediaprint',
                    array('required' => false)
                );

                $formMapper->add(
                    'binaryContent',
                    'file',
                    array('required' => false, 'label' => 'form.label_binary_content_new')
                );
            }
        }

        if (in_array($media->getProviderName(), $this->localisedMediaProviders)) {
            $formMapper->add(
                'locale',
                'choice',
                array(
                    'choices' => $this->getLocaleChoices(),
                )
            );
        }


        if ($this->getSubject() && $this->getSubject()->getId()) {
            $formMapper->add(
                'tags',
                'sonata_type_model',
                array(
                    'required' => false,
                    'expanded' => false,
                    'multiple' => true,
                    'help_label' => 'help.media_tag',
                    'taggable' => true,
                    'attr' => array('style' => "width:220px"),
                )
            );
        }

        //remove and re-add fields to control field order
        if ($formMapper->has('enabled')) {
            $formMapper->remove('enabled');
            $formMapper->add(
                'enabled',
                null,
                array('required' => false),
                array('inline_block' => true)
            );
        }

        if ($formMapper->has('cdnIsFlushable')) {
            $formMapper->remove('cdnIsFlushable');
            $formMapper->add(
                'cdnIsFlushable',
                null,
                array('required' => false),
                array('inline_block' => true)
            );
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add(
                'custom',
                'string',
                array(
                    'template' => 'NetworkingInitCmsBundle:MediaAdmin:list_custom.html.twig',
                    'label' => 'list.label_media'
                )
            );

        if ($this->request && $this->request->get('pcode') == '') {
            $listMapper->add(
                '_action',
                'actions',
                array(
                    'actions' => array(
                        'show' => array(),
                        'edit' => array(),
                        'delete' => array()
                    )
                )
            );
        }

    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper, $context = '')
    {

        $datagridMapper
            ->add('name', 'networking_init_cms_simple_string')
            ->add('tags')
            ->add('authorName', null, array('hidden' => true));

        if ($context) {
            $datagridMapper->add(
                'context',
                'networking_init_cms_simple_string',
                array('field_type' => 'hidden', 'label_render' => false)
            );

            $persistedParams = $this->getPersistentParameters();
            if (array_key_exists('context', $persistedParams) && $persistedParams['context'] == $context) {
                $datagridMapper->add(
                    'providerName',
                    'networking_init_cms_simple_string',
                    array('field_type' => 'hidden', 'label_render' => false)
                );

            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getDatagrid($context = '')
    {
        $this->buildDatagrid($context);

        return $this->datagrid;
    }

    /**
     * {@inheritdoc}
     */
    public function buildDatagrid($context = '')
    {
        if ($this->datagrid) {
            $this->datagrid = null;
            $this->filterFieldDescriptions = array();
        }

        $filterParameters = $this->getFilterParameters();


        if ($context && array_key_exists('context', $filterParameters)) {
            if ($filterParameters['context']['value'] != $context) {
                $filterParameters['_page'] = 1;
            }
        }


        // transform _sort_by from a string to a FieldDescriptionInterface for the datagrid.
        if (isset($filterParameters['_sort_by']) && is_string($filterParameters['_sort_by'])) {
            if ($this->hasListFieldDescription($filterParameters['_sort_by'])) {
                $filterParameters['_sort_by'] = $this->getListFieldDescription($filterParameters['_sort_by']);
            } else {
                $filterParameters['_sort_by'] = $this->getModelManager()->getNewFieldDescriptionInstance(
                    $this->getClass(),
                    $filterParameters['_sort_by'],
                    array()
                );

                $this->getListBuilder()->buildField(null, $filterParameters['_sort_by'], $this);
            }
        }

        // initialize the datagrid
        $this->datagrid = $this->getDatagridBuilder()->getBaseDatagrid($this, $filterParameters);

        $this->datagrid->getPager()->setMaxPageLinks($this->maxPageLinks);

        $mapper = new DatagridMapper($this->getDatagridBuilder(), $this->datagrid, $this);


        // build the datagrid filter
        $this->configureDatagridFilters($mapper, $context);

        // ok, try to limit to add parent filter
        if ($this->isChild() && $this->getParentAssociationMapping() && !$mapper->has(
                $this->getParentAssociationMapping()
            )
        ) {
            $mapper->add(
                $this->getParentAssociationMapping(),
                null,
                array(
                    'field_type' => 'sonata_type_model_reference',
                    'field_options' => array(
                        'model_manager' => $this->getModelManager()
                    ),
                    'operator_type' => 'hidden'
                )
            );
        }

        foreach ($this->getExtensions() as $extension) {
            $extension->configureDatagridFilters($mapper, $context);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getPersistentParameters()
    {
        if (!$this->hasRequest()) {
            return array();
        }

        if ($this->getSubject()) {
            $this->getRequest()->query->set('context', $this->getSubject()->getContext());
        }

        $contexts = $this->pool->getContexts();
        reset($contexts);
        $contextName = key($contexts);


        $context = $this->getRequest()->get('context', $contextName);
        $providers = $this->pool->getProvidersByContext($context);
        $provider = $this->getRequest()->get('provider');

        // if the context has only one provider, set it into the request
        // so the intermediate provider selection is skipped
        if (count($providers) == 1 && null === $provider) {
            $provider = array_shift($providers)->getName();
            $this->getRequest()->query->set('provider', $provider);
        }

        return array(
            'provider' => $provider,
            'context' => $context,
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getBatchActions()
    {
        if ($this->request && $this->request->get('pcode') == '') {
            return parent::getBatchActions();
        }

        return array();
    }

    /**
     * {@inheritdoc}
     */
    public function getExportFormats()
    {
        if ($this->request->get('pcode') == '') {
            return parent::getExportFormats();
        }

        return array();
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
     * {@inheritdoc}
     */
    public function generateUrl($name, array $parameters = array(), $absolute = false)
    {
        try {
            if ($this->getRequest()->get('pcode')) {
                $parameters['pcode'] = $this->getRequest()->get('pcode');
            }
        } catch (\Exception $e) {
            //do nothing
        }


        return parent::generateUrl($name, $parameters, $absolute);
    }

    public function setLocalisedMediaProviders(array $providers)
    {
        $this->localisedMediaProviders = $providers;
    }
}
