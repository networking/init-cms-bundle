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

    public function __construct(
        ?string $name = null,
        ?string $type = null,
        array $fieldDescriptionOptions = []
    ){
        $this->type = $type;
        $this->name = $name;
        $this->fieldDescriptionOptions = $fieldDescriptionOptions;
    }

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
