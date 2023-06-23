<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Annotation\Order;

interface FormReorderInterface extends ReorderInterface
{
    /**
     * @return string|null
     */
    public function getWith();
}
