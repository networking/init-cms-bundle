<?php

namespace Networking\InitCmsBundle\Annotation;

abstract class AbstractMapper
{
    /**
     * @var string
     */
    public $name = null;

    /**
     * @var string
     */
    public $type = null;

    /**
     * @var array
     */
    public $fieldDescriptionOptions = array();

    /**
     * @return null|string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return null|string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * @return array
     */
    public function getFieldDescriptionOptions()
    {
        return $this->fieldDescriptionOptions;
    }
}