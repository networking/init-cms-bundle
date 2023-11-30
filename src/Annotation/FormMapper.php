<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Annotation;

/**
 * @Annotation
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FormMapper extends AbstractMapper implements FormInterface
{
    /**
     * @var array
     */
    public $options = [];

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

    /**
     * @var bool
     */
    public $ignoreOnParent = false;

    public function __construct(
        string $type,
        array $options = [],
        array $fieldDescriptionOptions = [],
        array $withOptions = [],
        bool $ignoreOnParent = false,
        ?string $name = null,
        ?string $with = null,
        ?string $tab = null,
    ){
        $this->type = $type;
        $this->fieldDescriptionOptions = $fieldDescriptionOptions;
        $this->options = $options;
        $this->with = $with;
        $this->withOptions = $withOptions;
        $this->tab = $tab;
        $this->ignoreOnParent = $ignoreOnParent;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

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
     * @return boolean
     */
    public function isIgnoreOnParent()
    {
        return $this->ignoreOnParent;
    }

    public function getTab(): ?string
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
