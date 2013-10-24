<?php

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Document;

use Doctrine\Bundle\DoctrineBundle\Registry,
    Doctrine\ORM\EntityManager,
    Symfony\Component\Form\FormFactoryInterface,
    Symfony\Component\EventDispatcher\EventSubscriberInterface,
    Symfony\Component\Form\FormEvents,
    Symfony\Component\Form\FormEvent,
    Symfony\Component\Form\FormBuilder,
    Symfony\Component\Form\FormInterface,
    Networking\InitCmsBundle\Model\PageInterface,
    Networking\InitCmsBundle\Model\LayoutBlockInterface,
    Networking\InitCmsBundle\Admin\LayoutBlockAdmin,
    Networking\InitCmsBundle\Model\ContentInterface,
    Networking\InitCmsBundle\Form\DataTransformer\PageToNumberTransformer,
    Networking\InitCmsBundle\Helper\ContentInterfaceHelper,
    Ibrows\Bundle\SonataAdminAnnotationBundle\Reader\SonataAdminAnnotationReader;
use Networking\InitCmsBundle\Model\LayoutBlockFormListener as ModelLayoutBlockFormListener;
/**
 * @author net working AG <info@networking.ch>
 */
class LayoutBlockFormListener extends ModelLayoutBlockFormListener
{



    /**
     * Bind the content type objects variables from the form.
     * If needed create an new content type object, or change to a new type deleting the old one.
     * Set the Content objects contentType and objectId fields accordingly.
     *
     * @param \Symfony\Component\Form\FormEvent $event
     */
    public function postBindData(FormEvent $event)
    {

        /** @var $layoutBlock LayoutBlockInterface */
        $layoutBlock = $event->getForm()->getData();

        if (!$layoutBlock->getObjectId()) {
            $extraData = $event->getForm()->getExtraData();

            if (array_key_exists('content', $extraData)) {
                $layoutBlock->setContent($extraData['content']);
            }
        }

        /** @var $dr Registry */
        $dr = $this->container->get('Doctrine_mongodb');

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
        /** @var \Doctrine\ODM\MongoDB\Mapping\ClassMetadata $meta */
        $meta = $em->getClassMetadata(get_class($contentObject));

        foreach ($layoutBlock->getContent() as $key => $field) {

            try {
                $targetClass =  $meta->getAssociationTargetClass($key);


                $field = $dr->getRepository($targetClass)->find($field);

            } catch (\InvalidArgumentException $e) {
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
        $dr = $this->container->get('Doctrine_mongodb');

        $className = $this->getContentType($layoutBlock);

        $objectRepository = $dr->getRepository($className);

        if (!$layoutBlock->getObjectId() || !$contentObject = $objectRepository->findOneById($layoutBlock->getObjectId())) {
            $contentObject = new $className();
        }

        if (!$contentObject instanceof ContentInterface) {
            throw new \RuntimeException('Content Object must implement the ContentInterface');
        }

        $this->addFieldsToForm($form, $className, $contentObject);

    }
}
