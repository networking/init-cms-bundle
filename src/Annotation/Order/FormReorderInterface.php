<?php

namespace Networking\InitCmsBundle\Annotation\Order;

interface FormReorderInterface extends ReorderInterface
{
    /**
     * @return string|null
     */
    public function getWith();
}