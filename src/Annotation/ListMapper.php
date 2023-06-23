<?php

declare(strict_types=1);

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

    public function isIdentifier(): bool
    {
        return (bool)$this->identifier;
    }

    public function getRouteName(): ?string
    {
        return $this->routeName;
    }
}
