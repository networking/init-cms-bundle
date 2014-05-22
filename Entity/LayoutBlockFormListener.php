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

use Symfony\Component\Form\FormEvent;
use Networking\InitCmsBundle\Model\LayoutBlockInterface;
use Networking\InitCmsBundle\Model\ContentInterface;
use Networking\InitCmsBundle\Model\LayoutBlockFormListener as ModelLayoutBlockFormListener;

/**
 * Class LayoutBlockFormListener
 * @package Networking\InitCmsBundle\Entity
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
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

        $contentObject = $layoutBlock->getContent();

        if (!$contentObject instanceof ContentInterface) {
            throw new \RuntimeException('Content Object must implement the ContentInterface');
        }

       $this->validate($event, $contentObject);
    }



    /**
     * Create or retrieve the content type object
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

        $event->getForm();

        $className = $this->getContentType($layoutBlock);

        $objectRepository = $this->om->getRepository($className);

        if (!$layoutBlock->getObjectId() || !$contentObject = $objectRepository->findOneById(
                $layoutBlock->getObjectId()
            )
        ) {
            $contentObject = new $className();
        }

        if (!$contentObject instanceof ContentInterface) {
            throw new \RuntimeException('Content Object must implement the ContentInterface');
        }


        $layoutBlock->setContent($contentObject);


        $event->setData($layoutBlock);


    }
}
