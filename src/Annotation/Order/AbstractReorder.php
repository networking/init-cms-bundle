<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Annotation\Order;

use Networking\InitCmsBundle\Annotation\Order\ShowAndFormReorderInterface;

abstract class AbstractReorder implements ReorderInterface
{
    /**
     * @var array
     */
    public $keys = [];

    /**
     * @return array
     */
    public function getKeys()
    {
        return $this->keys;
    }
}
