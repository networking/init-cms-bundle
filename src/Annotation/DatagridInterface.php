<?php

namespace Networking\InitCmsBundle\Annotation;

interface DatagridInterface extends AdminInterface
{
    /**
     * @return array
     */
    public function getFilterOptions();

    /**
     * @return array
     */
    public function getFieldOptions();

    /**
     * @return string|null
     */
    public function getFieldType();
}