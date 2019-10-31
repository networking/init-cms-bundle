<?php

namespace Networking\InitCmsBundle\Annotation;

/**
 * @Annotation
 */
class ListMapper extends AbstractMapper implements ListInterface
{
    /**
     * @var boolean
     */
    public $identifier = false;

    /**
     * @var string
     */
    public $routeName = null;

    /**
     * @return boolean
     */
    public function isIdentifier()
    {
        return (bool)$this->identifier;
    }

    /**
     * @return null|string
     */
    public function getRouteName()
    {
        return $this->routeName;
    }
}