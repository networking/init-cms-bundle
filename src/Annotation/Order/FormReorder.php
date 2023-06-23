<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Annotation\Order;

/**
 * @Annotation
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class FormReorder extends AbstractReorder implements FormReorderInterface
{
    /**
     * @var string
     */
    public $with = null;

    public function getWith(): ?string
    {
        return $this->with;
    }
}
