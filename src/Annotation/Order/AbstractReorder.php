<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Annotation\Order;

abstract class AbstractReorder implements ReorderInterface
{
    /**
     * @var array
     */
    public $keys = [];

    public function __construct(array $keys)
    {
        $this->keys = $keys;
    }

    /**
     * @return array
     */
    public function getKeys()
    {
        return $this->keys;
    }
}
