<?php

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Entity;

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
     * @param FormEvent $event
     * @throws \RuntimeException
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



        /** if the content type has changed, find and remove the old one */
        if ($layoutBlock->getOrigClassType() && $layoutBlock->getOrigClassType() != $layoutBlock->getClassType()) {
            if ($classType = $layoutBlock->getOrigClassType()) {
                $oldRepository = $this->om->getRepository($classType);
                if ($oldContentTypeObject = $oldRepository->find($layoutBlock->getObjectId())) {
                    $this->om->remove($oldContentTypeObject);
                    $layoutBlock->setObjectId(null);
                    $layoutBlock->setContent(array());
                }
            }
        }

        $className = $this->getContentType($layoutBlock);
        $objectRepository = $this->om->getRepository($className);

        if ($layoutBlock->getObjectId()) {
            $contentObject = $objectRepository->find($layoutBlock->getObjectId());
        } else {
            $contentObject = new $className();
        }

        if (!$contentObject instanceof ContentInterface) {
            throw new \RuntimeException('Content Object must implement the ContentInterface');
        }

        $meta = $this->om->getClassMetadata(get_class($contentObject));

        foreach ($layoutBlock->getContent() as $key => $field) {

            try {
                $mapping = $meta->getAssociationMapping($key);

                $field = $this->om->getRepository($mapping['targetEntity'])->find($field);

            } catch (\Doctrine\ORM\Mapping\MappingException $e) {
                //do nothing
            }
            $contentObject = $this->contentInterfaceHelper->setFieldValue($contentObject, $key, $field);
        }

        $this->om->persist($contentObject);
        $this->om->flush();

        $layoutBlock->setObjectId($contentObject->getId());

        $this->om->persist($layoutBlock);
        $this->om->flush();
    }
}
