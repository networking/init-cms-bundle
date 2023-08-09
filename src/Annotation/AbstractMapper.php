<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Annotation;

abstract class AbstractMapper implements AdminInterface
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
    public $fieldDescriptionOptions = [];

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
