<?php

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Model;

use Doctrine\Bundle\DoctrineBundle\Registry,
    Symfony\Component\Form\FormFactoryInterface,
    Symfony\Component\EventDispatcher\EventSubscriberInterface,
    Symfony\Component\Form\FormEvents,
    Symfony\Component\Form\FormEvent,
    Symfony\Component\Form\FormBuilder,
    Symfony\Component\Form\FormInterface,
    Networking\InitCmsBundle\Admin\LayoutBlockAdmin,
    Networking\InitCmsBundle\Form\DataTransformer\PageToNumberTransformer,
    Networking\InitCmsBundle\Helper\ContentInterfaceHelper,
    Ibrows\Bundle\SonataAdminAnnotationBundle\Reader\SonataAdminAnnotationReader;

/**
 * @author net working AG <info@networking.ch>
 */
abstract class LayoutBlockFormListener implements EventSubscriberInterface, LayoutBlockFormListenerInterface
{
    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    protected $factory;
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    protected $container;
    /**
     * @var ContentInterfaceHelper $contentInterfaceHelper
     */
    protected $contentInterfaceHelper;

    /**
     * @var string $layoutBlockClass
     */
    protected $layoutBlockClass;

    /**
     * @param FormFactoryInterface $factory
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     * @param $class
     */
    public function __construct(
        \Symfony\Component\DependencyInjection\ContainerInterface $container,
        $class
    ) {
        $this->container = $container;
        $this->contentInterfaceHelper = new ContentInterfaceHelper;
        $this->layoutBlockClass = $class;

    }

    public function setFormFactory(FormFactoryInterface $factory){
        $this->factory = $factory;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_SUBMIT => 'preBindData',
            FormEvents::POST_SUBMIT => 'postBindData'
        );
    }

    /**
     * Prepare the content variables so that they can be accessed easily
     *
     * @param \Symfony\Component\Form\FormEvent $event
     */
    public function preBindData(FormEvent $event)
    {
        $submittedData = $event->getData();

        /** @var $layoutBlock LayoutBlockInterface */
        if (!$layoutBlock = $event->getForm()->getData()) {

            $layoutBlock = new $this->layoutBlockClass;
        }

        if (!array_key_exists('_delete', $submittedData) || $submittedData['_delete'] < 1) {

            $prefix = LayoutBlockAdmin::CUSTOM_FIELD_PREFIX;
            foreach ($submittedData as $key => $value) {
                if (!$value) {
                    continue;
                }
                if (substr($key, 0, strlen($prefix)) === $prefix) {
                    $layoutBlock->setContent($value, substr($key, strlen($prefix)));
                } else {
                    if ($key == 'page') {
                        // if field is Page, turn post value back into a Page Object
                        /** @var \Networking\InitCmsBundle\Model\PageManagerInterface $pageManager */
                        $pageManager = $this->container->get('networking_init_cms.page_manager');
                        $pageToNumberTransformer = new PageToNumberTransformer($pageManager);
                        $value = $pageToNumberTransformer->reverseTransform($value);
                    }
                    $this->contentInterfaceHelper->setFieldValue($layoutBlock, $key, $value);
                }
            }

            $event->getForm()->setData($layoutBlock);
        }
    }

    /**
     * Adds the extra form fields to the layoutBlock form
     *
     * @param \Symfony\Component\Form\FormInterface $form
     * @param $className
     * @param $contentObject
     */
    protected function addFieldsToForm(FormInterface $form, $className, $contentObject)
    {
        $annotations = $this->getSonataAnnotationReader()->getFormMapperAnnotations($contentObject);

        if ($annotations) {
            $this->createFormFieldsWithAnnotations($form, $annotations, $contentObject);
        } else {
            $fields = $className::getFieldDefinition();
            $this->createFormFieldsWithArray($form, $fields, $contentObject);
        }

        $form->remove('_delete');

        $formInterface =
            $this->factory->createNamed(
                '_delete',
                'checkbox',
                null,
                array('required' => false, 'mapped' => false, 'auto_initialize' => false)
            );
        $form->add($formInterface);
    }

    /**
     * Uses the annotations to create form fields for the content object
     *
     * @param \Symfony\Component\Form\FormInterface $form
     * @param $annotations
     * @param $contentObject
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function createFormFieldsWithAnnotations(FormInterface $form, $annotations, $contentObject)
    {
        $defaultValue = null;

        foreach ($annotations as $propertyName => $annotation) {

            $fieldName = $annotation->getName() ? : $propertyName;

            if ($contentObject) {
                $defaultValue = $this->contentInterfaceHelper->getFieldValue($contentObject, $fieldName);
            }
            $formInterface = $this->factory->createNamed(
                LayoutBlockAdmin::CUSTOM_FIELD_PREFIX . $fieldName,
                $annotation->getType(),
                $defaultValue,
                $annotation->getOptions()

            );

            $formInterface->getConfig()->setAutoInitialize(false);
            $form->add($formInterface);

        }

        return $form;
    }

    /**
     * Uses static method to get the form field configuration to create forms fields for
     * the content object
     *
     * @param \Symfony\Component\Form\FormInterface $form
     * @param $fields
     * @param $contentObject
     * @return \Symfony\Component\Form\FormInterface
     */
    protected function createFormFieldsWithArray(FormInterface $form, $fields, $contentObject)
    {
        $defaultValue = null;


        foreach ($fields as $field) {
            if ($contentObject) {
                $defaultValue = $this->contentInterfaceHelper->getFieldValue($contentObject, $field['name']);
            }
            $form->remove(LayoutBlockAdmin::CUSTOM_FIELD_PREFIX . $field['name']);
            $form->add(
                $this->factory->createNamed(
                    LayoutBlockAdmin::CUSTOM_FIELD_PREFIX . $field['name'],
                    $field['type'],
                    $defaultValue,
                    $field['options']
                )
            );
        }

        return $form;

    }

    /**
     * Get the content type of the content object, if the object is new, use the first available type
     *
     * @param  \Networking\InitCmsBundle\Entity\LayoutBlock $layoutBlock
     * @return string
     */
    public function getContentType(LayoutBlockInterface $layoutBlock)
    {
        if (!$classType = $layoutBlock->getClassType()) {
            $contentTypes = $this->container->getParameter('networking_init_cms.page.content_types');

            $classType = $contentTypes[0]['class'];
        }

        return $classType;
    }


    /**
     * @return SonataAdminAnnotationReader
     */
    protected function getSonataAnnotationReader()
    {
        return $this->container->get('ibrows_sonataadmin.annotation.reader');
    }


    /**
     * @return LayoutBlockAdmin
     */
    protected function getLayoutBlockAdmin()
    {
        return $this->container->get('networking_init_cms.page.admin.layout_block');
    }

}
