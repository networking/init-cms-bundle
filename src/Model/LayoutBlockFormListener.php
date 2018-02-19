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

use Doctrine\Common\Persistence\ObjectManager;
use Networking\InitCmsBundle\Form\Type\ContentType;
use Symfony\Component\Form\FormError;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\Form\FormEvent;
use Networking\InitCmsBundle\Admin\Model\LayoutBlockAdmin;
use Symfony\Component\Validator\Validator\TraceableValidator;

/**
 * @author net working AG <info@networking.ch>
 */
abstract class LayoutBlockFormListener implements EventSubscriberInterface, LayoutBlockFormListenerInterface
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $om;

    /**
     * @var array
     */
    protected $contentTypes;

    /**
     * @var TraceableValidator
     */
    protected $validator;

    /**
     * @var LayoutBlockAdmin
     */
    protected $admin;

    /**
     * @var string
     */
    protected $contentType;

    /**
     * @param ObjectManager $om
     */
    public function __construct(
        ObjectManager $om
    ) {
        $this->om = $om;

    }

    public function setValidator(TraceableValidator $validator)
    {
        $this->validator = $validator;
    }

    public function setAdmin(LayoutBlockAdmin $admin)
    {
        $this->admin = $admin;
    }

    /**
     * @return array $contentTypes
     */
    public function getContentTypes()
    {
        return $this->contentTypes;
    }

    /**
     */
    public function setContentTypes($contentTypes)
    {
        $this->contentTypes = $contentTypes;
    }

    /**
     * @return array
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'preSetData',
            FormEvents::POST_SUBMIT => 'postBindData'
        ];
    }

    public function setContentType($contentType)
    {
        $this->contentType = $contentType;
    }

    public function preSetData(FormEvent $event)
    {
        $layoutBlock = $event->getData();

        $form = $event->getForm();
        $form->add(
            'content',
            ContentType::class,
            [
                'label_render' => false,
                'label' => false,
                'class' => $this->getContentType($layoutBlock),
                'widget_form_group' => false
            ]
        );
        $form->remove('_delete');
    }

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

    public function validate(FormEvent &$event, $contentObject)
    {
        /** @var $layoutBlock LayoutBlockInterface */
        $form = $event->getForm();

        /** @var \Symfony\Component\Validator\ConstraintViolationList $errors */
        $errors = $this->validator->validate($contentObject);

        if (count($errors) > 0) {

            if ($contentObject->getId()) {
                $message = $this->admin->getTranslator()->trans(
                    'message.layout_block_not_edited',
                    [],
                    $this->admin->getTranslationDomain()
                );

            } else {
                $message = $this->admin->getTranslator()->trans(
                    'message.layout_block_not_created',
                    [],
                    $this->admin->getTranslationDomain()
                );
            }
            $form->addError(new FormError($message));

            /** @var \Symfony\Component\Validator\ConstraintViolation $error */
            foreach ($errors->getIterator() as $error) {
                $fieldName = $error->getPropertyPath();
                /** @var \Symfony\Component\Form\Form $field */
                $field = $form->get('content')->get($fieldName);
                $field->addError(
                    new FormError($error->getMessage(), $error->getMessageTemplate(), $error->getMessageParameters(
                    ), $error->getMessagePluralization())
                );
            }
        }

        return $event;
    }


    /**
     * Get the content type of the content object, if the object is new, use the first available type
     *
     * @param LayoutBlockInterface $layoutBlock
     * @return string
     */
    public function getContentType(LayoutBlockInterface $layoutBlock = null)
    {
        if (is_null($layoutBlock) || !$classType = $layoutBlock->getClassType()) {

            if ($this->contentType) {
                return $this->contentType;
            }

            $contentTypes = $this->getContentTypes();

            $classType = $contentTypes[0]['class'];
        }

        return $classType;
    }


}
