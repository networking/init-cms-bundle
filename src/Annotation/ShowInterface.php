<?php

namespace Networking\InitCmsBundle\Annotation;

interface ShowInterface extends AdminInterface
{
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
}