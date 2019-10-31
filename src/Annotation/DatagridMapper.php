<?php

namespace Networking\InitCmsBundle\Annotation;

/**
 * @Annotation
 */
class DatagridMapper extends AbstractMapper implements DatagridInterface
{
    /**
     * @var array
     */
    public $filterOptions = array();

    /**
     * @var array
     */
    public $fieldOptions = array();

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

    /**
     * @return string|null
     */
    public function getFieldType(){
        return $this->fieldType;
    }
}