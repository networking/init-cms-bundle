<?php

namespace Networking\InitCmsBundle\Annotation;

interface AdminInterface
{
    /**
     * @return null|string
     */
    public function getName();

    /**
     * @return null|string
     */
    public function getType();

    /**
     * @return array
     */
    public function getFieldDescriptionOptions();
}