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
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Form\FormMapper;

use Networking\InitCmsBundle\Form\DataTransformer\PageToNumberTransformer;
use Networking\InitCmsBundle\EventListener\LayoutBlockFormListener;

/**
 * @author net working AG <info@networking.ch>
 */
class LayoutBlockAdmin extends BaseAdmin
{

    /**
     * used to prefix dynamically generated form fields
     */
    const CUSTOM_FIELD_PREFIX = 'networking_init_cms_content_';

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {

        $listener = new LayoutBlockFormListener($formMapper->getFormBuilder()->getFormFactory(), $this->getContainer());
        $formMapper->getFormBuilder()->addEventSubscriber($listener);

        $entityManager = $this->getContainer()->get('Doctrine')->getManager();
        $transformer = new PageToNumberTransformer($entityManager);
        $formMapper
//                ->add('isActive', 'checkbox', array('required' => false, 'label_render' => false))
                ->add('zone',
                    'hidden'
                    )
                ->add($formMapper->getFormBuilder()->create('page', 'hidden')
                                    ->addModelTransformer($transformer))
                ->add('classType', 'hidden')
                ->add('sortOrder', 'hidden');
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
                ->add('name')
                ->add('page');
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $listMapper
                ->addIdentifier('name')
                ->add('page')
                ->add('isActive');
    }

    /**
     * @param \Sonata\AdminBundle\Validator\ErrorElement $errorElement
     * @param mixed                                      $object
     */
    public function validate(ErrorElement $errorElement, $object)
    {
        $errorElement
                ->with('name')
                ->assertMaxLength(array('limit' => 255))
                ->end();
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
