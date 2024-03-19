<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Annotation;

/**
 * @Annotation
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class FormMapper extends AbstractMapper implements FormInterface
{
    public array $options = [];

    public ?string $with = null;

    public array $withOptions = [];

    public ?string $tab = null;

    public $tabOptions = [];

    public $ignoreOnParent = false;

    public function __construct(
        ?string $name = null,
        ?string $type = null,
        array $fieldDescriptionOptions = [],
        array $options = [],
        array $withOptions = [],
        bool $ignoreOnParent = false,
        ?string $with = null,
        ?string $tab = null,
    ) {
        $this->options = $options;
        $this->with = $with;
        $this->withOptions = $withOptions;
        $this->tab = $tab;
        $this->ignoreOnParent = $ignoreOnParent;

        parent::__construct($name, $type, $fieldDescriptionOptions);
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
     * @return bool
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
