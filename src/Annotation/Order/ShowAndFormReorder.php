<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Annotation\Order;

/**
 * @Annotation
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class ShowAndFormReorder extends AbstractReorder implements ShowAndFormReorderInterface
{
    /**
     * @var string
     */
    public ?string $with = null;

    public function __construct(array $keys, ?string $with = null)
    {
        parent::__construct($keys);
        $this->with = $with;
    }

    public function getWith(): ?string
    {
        return $this->with;
    }
}
