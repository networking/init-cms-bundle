<?php

/*
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Registry;
use Doctrine\ORM\EntityManager;

use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormBuilder;

use Networking\InitCmsBundle\Entity\LayoutBlock;
use Networking\InitCmsBundle\Admin\LayoutBlockAdmin;
use Networking\InitCmsBundle\Entity\ContentInterface;
use Networking\InitCmsBundle\Form\DataTransformer\PageToNumberTransformer;
use Networking\InitCmsBundle\Helper\ContentInterfaceHelper;

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
     * @param \Symfony\Component\Form\FormFactoryInterface              $factory
     * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    public function __construct(FormFactoryInterface $factory, \Symfony\Component\DependencyInjection\ContainerInterface $container)
    {
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
//			FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::POST_SET_DATA => 'postSetData',
            FormEvents::PRE_BIND => 'preBindData',
            FormEvents::POST_BIND => 'postBindData'
        );
    }

    /**
     * @param \Symfony\Component\Form\FormEvent $event
     */
    public function preSetData(FormEvent $event)
    {
        /** @var $deleteField FormBuilder */
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
                if(!$value) continue;
                if (substr($key, 0, strlen($prefix)) === $prefix) {
                        $layoutBlock->setContent($value, substr($key, strlen($prefix)));
                } else {
                    if ($key == 'page') {
                        // if field is Page, turn post value back into a Page Object
                        $em = $this->container->get('doctrine')->getEntityManager();
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
            $contentObject->setLayoutBlock($layoutBlock);
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
     * @param  \Symfony\Component\Form\FormEvent $event
     * @throws \RuntimeException
     */
    public function postSetData(FormEvent $event)
    {

        $layoutBlock = $event->getData();

        if (!$layoutBlock) return;

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

        $fields = $className::getFieldDefinition();
        $defaults = null;

        foreach ($fields as $field) {
            if ($contentObject) {
                $defaults = $this->contentInterfaceHelper->getFieldValue($contentObject, $field['name']);
            }
            $form->remove(LayoutBlockAdmin::CUSTOM_FIELD_PREFIX . $field['name']);
            $form->add($this->factory->createNamed(LayoutBlockAdmin::CUSTOM_FIELD_PREFIX . $field['name'], $field['type'], $defaults, $field['options']));
        }

        $form->remove('_delete');
        $form->add($this->factory->createNamed('_delete', 'checkbox', null, array('required' => false, 'property_path' => false)));
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

}
