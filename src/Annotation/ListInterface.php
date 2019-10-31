<?php

namespace Networking\InitCmsBundle\Annotation;

interface ListInterface extends AdminInterface
{
    /**
     * @return boolean
     */
    public function isIdentifier();

    /**
     * @return string|null
     */
    public function getRouteName();
}