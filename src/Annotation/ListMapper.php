<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Annotation;

/**
 * @Annotation
 */
#[\Attribute(\Attribute::TARGET_PROPERTY)]
class ListMapper extends AbstractMapper implements ListInterface
{
    /**
     * @var bool
     */
    public $identifier = false;

    /**
     * @var string
     */
    public $routeName;

    public function __construct(
        ?string $name = null,
        ?string $type = null,
        array $fieldDescriptionOptions = [],
        bool $identifier = false,
        ?string $routeName = null
    ) {
        $this->identifier = $identifier;
        $this->routeName = $routeName;
        parent::__construct($name, $type, $fieldDescriptionOptions);
    }

    public function isIdentifier(): bool
    {
        return (bool) $this->identifier;
    }

    public function getRouteName(): ?string
    {
        return $this->routeName;
    }
}
