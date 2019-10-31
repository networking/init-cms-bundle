<?php

namespace Networking\InitCmsBundle\Annotation;

/**
 * @Annotation
 */
class FormMapper extends AbstractMapper implements FormInterface
{
    /**
     * @var array
     */
    public $options = array();

    /**
     * @var string
     */
    public $with = null;

    /**
     * @var array
     */
    public $withOptions = array();

    /**
     * @var string
     */
    public $tab = null;

    /**
     * @var array
     */
    public $tabOptions = array();

    /**
     * @var bool
     */
    public $ignoreOnParent = false;

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @return null|string
     */
    public function getWith()
    {
        return $this->with;
    }

    /**
     * @return array
     */
    public function getWithOptions()
    {
        return $this->withOptions;
    }

    /**
     * @return boolean
     */
    public function isIgnoreOnParent()
    {
        return $this->ignoreOnParent;
    }

    /**
     * @return string|null
     */
    public function getTab()
    {
        return $this->tab;
    }

    /**
     * @return array
     */
    public function getTabOptions()
    {
        return $this->tabOptions;
    }
}