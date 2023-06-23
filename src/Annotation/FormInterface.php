<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Annotation;

interface FormInterface extends AdminInterface
{
    /**
     * @return array
     */
    public function getOptions();

    /**
     * @return string|null
     */
    public function getWith();

    /**
     * @return array
     */
    public function getWithOptions();

    /**
     * @return string|null
     */
    public function getTab();

    /**
     * @return array
     */
    public function getTabOptions();

    /**
     * @return boolean
     */
    public function isIgnoreOnParent();
}
