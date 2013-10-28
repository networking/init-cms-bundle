<?php

/**
 * This file is part of the Networking package.
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Admin;

use Sonata\AdminBundle\Admin\AdminHelper as SonataAdminHelper;
use Sonata\AdminBundle\Admin\FieldDescriptionInterface;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Exception\NoValueException;

/**
 * Overrides the SonataAdminHelper to be used in networking_init_cms admin controllers
 *
 * @author net working AG <info@networking.ch>
 */
class PageAdminHelper extends SonataAdminHelper
{
    /**
     * @var array $newLayoutBlockParameters
     */
    protected $newLayoutBlockParameters;

    /**
     * @param AdminInterface $admin
     * @param object $subject
     * @param string $elementId
     * @return array
     */
    public function appendFormFieldElement(AdminInterface $admin, $subject, $elementId)
    {
        // retrieve the subject
        $formBuilder = $admin->getFormBuilder();

        $form = $formBuilder->getForm();
        $form->setData($subject);
        $form->submit($admin->getRequest());

        // get the field element
        $childFormBuilder = $this->getChildFormBuilder($formBuilder, $elementId);

        // retrieve the FieldDescription
        $fieldDescription = $admin->getFormFieldDescription($childFormBuilder->getName());

        try {
            $value = $fieldDescription->getValue($form->getData());
        } catch (NoValueException $e) {
            $value = null;
        }

        // retrieve the posted data
        $data = $admin->getRequest()->get($formBuilder->getName());

        if (!isset($data[$childFormBuilder->getName()])) {
            $data[$childFormBuilder->getName()] = array();
        }

        $objectCount = count($value);
        $postCount = count($data[$childFormBuilder->getName()]);

        $fields = array_keys($fieldDescription->getAssociationAdmin()->getFormFieldDescriptions());

        // for now, not sure how to do that
        $value = array();
        foreach ($fields as $name) {
            $value[$name] = '';
        }

        // add new elements to the subject
        while ($objectCount <= $postCount) {
            // append a new instance into the object
            $this->addNewInstance($form->getData(), $fieldDescription);
            $objectCount++;
        }

        $subject->orderLayoutBlocks();

        $finalForm = $admin->getFormBuilder()->getForm();
        $finalForm->setData($subject);

        // bind the data
        $finalForm->setData($form->getData());

        return array($fieldDescription, $finalForm);
    }

    /**
     * @param object $object
     * @param FieldDescriptionInterface $fieldDescription
     * @throws \RuntimeException
     */
    public function addNewInstance($object, FieldDescriptionInterface $fieldDescription)
    {
        $instance = $fieldDescription->getAssociationAdmin()->getNewInstance();

        foreach ($this->newLayoutBlockParameters as $attr => $value) {
            $method = sprintf('set%s', $this->camelize($attr));
            $instance->$method($value);
        }

        $mapping = $fieldDescription->getAssociationMapping();

        $method = sprintf('add%s', $this->camelize($mapping['fieldName']));

        if (!method_exists($object, $method)) {
            $method = rtrim($method, 's');

            if (!method_exists($object, $method)) {
            throw new \RuntimeException(sprintf('Please add a method %s in the %s class!', $method, get_class($object)));
        }
        }

        $object->$method($instance);
    }

    /**
     * @param array $data
     */
    public function setNewLayoutBlockParameters(array $data)
    {
        $this->newLayoutBlockParameters = $data;
    }
}
