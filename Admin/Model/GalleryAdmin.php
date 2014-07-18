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

use Sonata\MediaBundle\Admin\GalleryAdmin as SonataGalleryAdmin;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

/**
 * Class GalleryAdmin
 * @package Networking\InitCmsBundle\Admin\Model
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class GalleryAdmin extends SonataGalleryAdmin
{
    protected $baseRoutePattern = 'cms/gallery';

    /**
     * @var array
     */
    protected $trackedActions = array('list');


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
        return 'glyph-camera-retro';
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $context = $this->getPersistentParameter('context');

        if (!$context) {
            $context = $this->pool->getDefaultContext();
        }

        $formats = array();
        foreach ((array)$this->pool->getFormatNamesByContext($context) as $name => $options) {
            $formats[$name] = $name;
        }

        $contexts = array();
        foreach ((array)$this->pool->getContexts() as $contextItem => $format) {
            $contexts[$contextItem] = $contextItem;
        }

        $formMapper
            ->add('enabled', null, array('required' => false), array('inline_block' => true))
            ->add('context', 'hidden')
            ->add('name')
            ->add('defaultFormat', 'choice', array('choices' => $formats))
            ->add(
                'galleryHasMedias',
                'sonata_type_collection',
                array(
                    'cascade_validation' => true,
                ),
                array(
                    'edit' => 'inline',
                    'inline' => 'table',
                    'sortable' => 'position',
                    'link_parameters' => array('context' => $context),
                )
            );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->add('enabled', null, array('editable' => true))
            ->addIdentifier('name')
            ->add('context', 'trans', array('catalogue' => 'SonataMediaBundle'))
            ->add('defaultFormat', 'trans', array('catalogue' => 'SonataMediaBundle'));

        $listMapper->add(
            '_action',
            'actions',
            array(
                'actions' => array(
                    'edit' => array(),
                    'delete' => array()
                )
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper, $context = '')
    {
        $datagridMapper
            ->add('name', 'networking_init_cms_simple_string')
            ->add('enabled', null, array('hidden' => true));

        if ($context) {
            $datagridMapper->add('context', null, array('hidden' => true));
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
}
