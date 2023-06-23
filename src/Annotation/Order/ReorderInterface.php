<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Annotation\Order;

interface ReorderInterface
{
    /**
     * @return array
     */
    public function getKeys();
}
