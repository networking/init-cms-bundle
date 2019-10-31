<?php

namespace Networking\InitCmsBundle\Annotation\Order;

interface ReorderInterface
{
    /**
     * @return array
     */
    public function getKeys();
}