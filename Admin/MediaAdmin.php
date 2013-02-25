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

use Sonata\MediaBundle\Admin\ORM\MediaAdmin as SonataMediaAdmin,
    Sonata\AdminBundle\Route\RouteCollection,
    Sonata\AdminBundle\Form\FormMapper,
    Sonata\MediaBundle\Form\DataTransformer\ProviderDataTransformer,
    Sonata\AdminBundle\Datagrid\ListMapper,
    Sonata\AdminBundle\Datagrid\DatagridMapper,
    Sonata\AdminBundle\Admin\AdminInterface,
    Knp\Menu\ItemInterface as MenuItemInterface;

/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class MediaAdmin extends SonataMediaAdmin
{
    /**
     * @var string
     */
    protected $baseRoutePattern = 'cms/media';

    /**
     * @var array
     */
    protected $trackedActions = array('list');

    /**
     * @return array
     */
    public function getSubNavLinks()
    {
        $pool = $this->getConfigurationPool();
        $galleryAdmin = $pool->getAdminByAdminCode('sonata.media.admin.gallery');
        $tagAdmin = $pool->getAdminByAdminCode('networking_init_cms.page.admin.tag');

        $links = array(
            $this->trans($this->getLabel()) => $this,
            $galleryAdmin->trans($galleryAdmin->getLabel()) => $galleryAdmin,
            $tagAdmin->trans($tagAdmin->getLabel()) => $tagAdmin
        );

        return $links;
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
     * @param \Sonata\AdminBundle\Route\RouteCollection $collection
     */
    public function configureRoutes(RouteCollection $collection)
    {
        parent::configureRoutes($collection);
        $collection->add('uploadTextBlockImage', 'upload_text_block_image', array(), array('method' => 'POST'));
        $collection->add('uploadedTextBlockImage', 'uploaded_text_block_image', array(), array('method' => 'GET'));
        $collection->add('uploadTextBlockFile', 'upload_text_block_file', array(), array('method' => 'POST'));
        $collection->add('uploadedTextBlockFile', 'uploaded_text_block_file', array(), array('method' => 'GET'));
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return 'icon-picture';
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
            new ProviderDataTransformer($this->pool, array('provider' => $media->getProviderName())),
            true
        );
        $provider = $this->pool->getProvider($media->getProviderName());

        if ($media->getId()) {
            $provider->buildEditForm($formMapper);
        } else {
            $provider->buildCreateForm($formMapper);
        }

        if ($formMapper->has('enabled')) {
            $formMapper->add(
                'enabled',
                null,
                array('required' => false),
                array('inline_block' => true)
            );
        }

        if ($formMapper->has('cdnIsFlushable')) {
            $formMapper->add(
                'cdnIsFlushable',
                null,
                array('required' => false),
                array('inline_block' => true)
            );
            $formMapper->reorder(array('name', 'enabled', 'cdnIsFlushable'));
        }


        if ($this->getSubject() && $this->getSubject()->getId()) {
            $formMapper->add(
                'tags',
                'sonata_type_model',
                array('required' => false, 'expanded' => true, 'multiple' => true)
            );
        }
    }

    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('custom', 'string', array('template' => 'NetworkingInitCmsBundle:MediaAdmin:list_custom.html.twig'));

        if ($this->request->get('pcode') == '') {
            $listMapper->add(
                '_action',
                'actions',
                array(
                    'actions' => array(
                        'view' => array(),
                        'edit' => array(),
                        'delete' => array()
                    )
                )
            );
        }

    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\DatagridMapper $datagridMapper
     * @return void
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper, $context = '')
    {

        $datagridMapper
            ->add('name', 'networking_init_cms_simple_string')
            ->add('tags', null, array('hidden' => true));

        if ($context) {
            $datagridMapper->add('context', null, array('hidden' => true));
        }

        $providers = array();

        if (!$context) {
            $context = $this->pool->getDefaultContext();
        }

        $providerNames = (array)$this->pool->getProviderList();

//        echo $this->pool->getDefaultContext()."!!";

        foreach ($providerNames as $name) {
            $providers[$name] = $name;
        }

        $datagridMapper->add(
            'providerName',
            'doctrine_orm_choice',
            array('hidden' => true),
            'sonata_type_translatable_choice',
            array('choices' => $providers, 'catalogue' => 'SonataMediaBundle')
        );
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

        $context = $this->getRequest()->get('context', $this->pool->getDefaultContext());
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
    protected function configureSideMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {
        if (!in_array($action, array('edit', 'view'))) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;

        $id = $this->getRequest()->get('id');

        $menu->setCurrentUri($this->request->getRequestUri());

        $menu->addChild(
            $this->trans('sidemenu.link_edit_media'),
            array('uri' => $admin->generateUrl('edit', array('id' => $id)))
        );

        $menu->addChild(
            $this->trans('sidemenu.link_media_view'),
            array('uri' => $admin->generateUrl('view', array('id' => $id)))
        );
    }

    /**
     * @return array
     */
    public function getBatchActions()
    {
        if ($this->request->get('pcode') == '') {
            return parent::getBatchActions();
        }

        return array();
    }

    /**
     * @return array
     */
    public function getExportFormats()
    {
        if ($this->request->get('pcode') == '') {
            return parent::getExportFormats();
        }

        return array();
    }
}
