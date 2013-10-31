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
     * used to prefix dynamically generated form fields
     */
    const CUSTOM_FIELD_PREFIX = 'networking_init_cms_content_';

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        /** @var \Networking\InitCmsBundle\Model\LayoutBlockFormListener $listener */
        $listener = $this->getContainer()->get('networking_init_cms.layout_block_form_listener');
        $listener->setFormFactory($formMapper->getFormBuilder()->getFormFactory());

        $formMapper->getFormBuilder()->addEventSubscriber($listener);

        $pageManager = $this->getContainer()->get('networking_init_cms.page_manager');
        $transformer = new PageToNumberTransformer($pageManager);
        $formMapper
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
     * {@inheritdoc}
     */
    public function validate(ErrorElement $errorElement, $object)
    {
        $errorElement
            ->with('name')
            ->assertLength(array('max' => 255))
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
