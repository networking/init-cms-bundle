<?php

namespace Networking\InitCmsBundle\Annotation\Order;

use Networking\InitCmsBundle\Annotation\Order\ShowAndFormReorderInterface;

abstract class AbstractReorder implements ReorderInterface
{
    /**
     * @var array
     */
    public $keys = array();

    /**
     * @return array
     */
    public function getKeys()
    {
        return $this->keys;
    }
}