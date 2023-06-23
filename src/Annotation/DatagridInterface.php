<?php

declare(strict_types=1);

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
