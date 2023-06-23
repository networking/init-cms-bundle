<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Annotation;

/**
 * @Annotation
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class DatagridMapper extends AbstractMapper implements DatagridInterface
{
    /**
     * @var array
     */
    public $filterOptions = [];

    /**
     * @var array
     */
    public $fieldOptions = [];

    /**
     * @var string
     */
    public $fieldType = null;

    /**
     * @return array
     */
    public function getFilterOptions()
    {
        return $this->filterOptions;
    }

    /**
     * @return array
     */
    public function getFieldOptions(){
        return $this->fieldOptions;
    }

    public function getFieldType(): ?string{
        return $this->fieldType;
    }
}
