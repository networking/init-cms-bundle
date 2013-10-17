<?php

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Registry,
    Doctrine\ORM\EntityManager,
    Symfony\Component\Form\FormFactoryInterface,
    Symfony\Component\EventDispatcher\EventSubscriberInterface,
    Symfony\Component\Form\FormEvents,
    Symfony\Component\Form\FormEvent,
    Symfony\Component\Form\FormBuilder,
    Symfony\Component\Form\FormInterface,
    Networking\InitCmsBundle\Entity\BasePage as Page,
    Networking\InitCmsBundle\Entity\LayoutBlock,
    Networking\InitCmsBundle\Admin\LayoutBlockAdmin,
    Networking\InitCmsBundle\Entity\ContentInterface,
    Networking\InitCmsBundle\Form\DataTransformer\PageToNumberTransformer,
    Networking\InitCmsBundle\Helper\ContentInterfaceHelper,
    Ibrows\Bundle\SonataAdminAnnotationBundle\Reader\SonataAdminAnnotationReader;

/**
 * @author net working AG <info@networking.ch>
 */
class LayoutBlockFormListener implements EventSubscriberInterface
{
    /**
     * @var \Symfony\Component\Form\FormFactoryInterface
     */
    private $factory;
    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface
     */
    private $container;
    /**
     * @var ContentInterfaceHelper $contentInterfaceHelper
     */
    private $contentInterfaceHelper;

    /**
     * @param \Symfony\Component\Form\FormFactoryInterface $factory
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(
        FormFactoryInterface $factory,
        \Symfony\Component\DependencyInjection\ContainerInterface $container
    ) {
        $this->factory = $factory;
        $this->container = $container;
        $this->contentInterfaceHelper = new ContentInterfaceHelper;

    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return array(
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::PRE_BIND => 'preBindData',
            FormEvents::POST_BIND => 'postBindData'
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

        /** @var $layoutBlock LayoutBlock */
        if (!$layoutBlock = $event->getForm()->getData()) {
            $layoutBlock = new LayoutBlock();
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
                        $em = $this->container->get('doctrine')->getManager();
                        $pageToNumberTransformer = new PageToNumberTransformer($em);
                        $value = $pageToNumberTransformer->reverseTransform($value);
                    }
                    $this->contentInterfaceHelper->setFieldValue($layoutBlock, $key, $value);
                }
            }

            $event->getForm()->setData($layoutBlock);
        }
    }

    /**
     * Bind the content type objects variables from the form.
     * If needed create an new content type object, or change to a new type deleting the old one.
     * Set the Content objects contentType and objectId fields accordingly.
     *
     * @param \Symfony\Component\Form\FormEvent $event
     */
    public function postBindData(FormEvent $event)
    {

        /** @var $layoutBlock LayoutBlock */
        $layoutBlock = $event->getForm()->getData();

        if (!$layoutBlock->getObjectId()) {
            $extraData = $event->getForm()->getExtraData();

            if (array_key_exists('content', $extraData)) {
                $layoutBlock->setContent($extraData['content']);
            }
        }

        /** @var $dr Registry */
        $dr = $this->container->get('Doctrine');

        /** @var $em EntityManager */
        $em = $dr->getManager();

        /** if the content type has changed, find and remove the old one */
        if ($layoutBlock->getOrigClassType() && $layoutBlock->getOrigClassType() != $layoutBlock->getClassType()) {
            if ($classType = $layoutBlock->getOrigClassType()) {
                $oldRepository = $dr->getRepository($classType);
                if ($oldContentTypeObject = $oldRepository->find($layoutBlock->getObjectId())) {
                    $em->remove($oldContentTypeObject);
                    $layoutBlock->setObjectId(null);
                    $layoutBlock->setContent(array());
                }
            }
        }

        $className = $this->getContentType($layoutBlock);
        $objectRepository = $dr->getRepository($className);

        if ($layoutBlock->getObjectId()) {
            $contentObject = $objectRepository->find($layoutBlock->getObjectId());
        } else {
            $contentObject = new $className();
        }

        if (!$contentObject instanceof ContentInterface) {
            throw new \RuntimeException('Content Object must implement the ContentInterface');
        }

        $meta = $em->getClassMetadata(get_class($contentObject));

        foreach ($layoutBlock->getContent() as $key => $field) {

            try {
                $mapping = $meta->getAssociationMapping($key);

                $field = $dr->getRepository($mapping['targetEntity'])->find($field);

            } catch (\Doctrine\ORM\Mapping\MappingException $e) {
                //do nothing
            }
            $contentObject = $this->contentInterfaceHelper->setFieldValue($contentObject, $key, $field);
        }

        $em->persist($contentObject);
        $em->flush();

        $layoutBlock->setObjectId($contentObject->getId());

        $em->persist($layoutBlock);
        $em->flush();
    }

    /**
     * Adds the form fields for the content object to the layoutBlock form
     *
     * @param  \Symfony\Component\Form\FormEvent $event
     * @throws \RuntimeException
     */
    public function preSetData(FormEvent $event)
    {

        $layoutBlock = $event->getData();

        if (!$layoutBlock) {
            return;
        }

        $form = $event->getForm();

        /** @var $dr Registry */
        $dr = $this->container->get('Doctrine');

        $className = $this->getContentType($layoutBlock);

        $objectRepository = $dr->getRepository($className);

        if (!$contentObject = $objectRepository->findOneById($layoutBlock->getObjectId())) {
            $contentObject = new $className();
        }

        if (!$contentObject instanceof ContentInterface) {
            throw new \RuntimeException('Content Object must implement the ContentInterface');
        }

        $this->addFieldsToForm($form, $className, $contentObject);

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
    public function getContentType(LayoutBlock $layoutBlock)
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
