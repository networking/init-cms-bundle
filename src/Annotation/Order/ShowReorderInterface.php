<?php

namespace Networking\InitCmsBundle\Annotation\Order;

interface ShowReorderInterface extends ReorderInterface
{
    /**
     * @return string|null
     */
    public function getWith();
}