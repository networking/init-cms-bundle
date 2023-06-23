<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Annotation\Order;

interface ShowAndFormReorderInterface extends ShowReorderInterface, FormReorderInterface
{
    /**
     * @return string|null
     */
    public function getWith();
}
