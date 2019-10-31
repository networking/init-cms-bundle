<?php

namespace Networking\InitCmsBundle\Annotation\Order;

interface ShowAndFormReorderInterface extends ShowReorderInterface, FormReorderInterface
{
    /**
     * @return string|null
     */
    public function getWith();
}