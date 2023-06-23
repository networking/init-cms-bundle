<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Annotation;

/**
 * @Annotation
 */
class ShowMapper extends AbstractMapper implements ShowInterface
{
    /**
     * @var string
     */
    public $with = null;

    /**
     * @var array
     */
    public $withOptions = [];

    /**
     * @var string
     */
    public $tab = null;

    /**
     * @var array
     */
    public $tabOptions = [];

    public function getWith(): ?string
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
     * @return string
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
