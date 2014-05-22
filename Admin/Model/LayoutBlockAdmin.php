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

use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Form\DataTransformer\ArrayToModelTransformer;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Form\FormMapper;
use Networking\InitCmsBundle\Form\DataTransformer\PageToNumberTransformer;
use Networking\InitCmsBundle\Admin\BaseAdmin;

/**
 * Class LayoutBlockAdmin
 * @package Networking\InitCmsBundle\Admin\Model
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class LayoutBlockAdmin extends BaseAdmin
{


    /**
     * @var string
     */
    protected $baseRoutePattern = 'cms/layout_block';
    /**
     * used to prefix dynamically generated form fields
     */
    const CUSTOM_FIELD_PREFIX = 'networking_init_cms_content_';

    /**
     * {@inheritdoc}
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('deleteAjax', 'delete_ajax', array(), array('method' => 'POST'));
        $collection->add('updateFormFieldElement', 'update_form_fields', array(), array('method' => 'POST'));
        $collection->add('updateLayoutBlockSort', 'update_layout_block_sort', array(), array('method' => 'GET'));

    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var \Networking\InitCmsBundle\Model\LayoutBlockFormListener $listener */
        $listener = $this->getContainer()->get('networking_init_cms.layout_block_form_listener');
        $listener->setAdmin($this);
        $formMapper->getFormBuilder()->addEventSubscriber($listener);

        $pageManager = $this->getContainer()->get('networking_init_cms.page_manager');
        $transformer = new PageToNumberTransformer($pageManager);


        if ($this->getSubject()) {
            $classType = $this->getSubject()->getClassType();
        } else {
            $classType = $this->getRequest()->get('classType');
        }

        $formMapper
            ->add(
                'content',
                'networking_type_content_block',
                array(
                    'error_bubbling' => false,
                    'data_class' => $classType)
            )
            ->add(
                'zone',
                'hidden'
            )
            ->add(
                $formMapper->getFormBuilder()->create('page', 'hidden')
                    ->addModelTransformer($transformer),
                'hidden'
            )
            ->add('classType', 'hidden')
            ->add('sortOrder', 'hidden');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add('name')
            ->add('page');
    }

    /**
     * {@inheritdoc}
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
            ->addIdentifier('name')
            ->add('page')
            ->add('isActive');
    }

    /**
     * @return array
     */
    public function getContentTypes()
    {
        $contentTypes = $this->getContainer()->getParameter('networking_init_cms.page.content_types');

        $choices = array();
        foreach ($contentTypes as $contentType) {
            $choices[$contentType['class']] = $contentType['name'];
        }

        return $choices;
    }

}
