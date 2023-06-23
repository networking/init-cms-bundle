<?php

declare(strict_types=1);

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Component\EventDispatcher;

use Symfony\Contracts\EventDispatcher\Event;

/**
 * Class CmsEvent.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class CmsEvent extends Event
{
    /**
     * @param $entity
     */
    public function __construct(
        /**
         * @var
         */
        protected $entity
    )
    {
    }

    /**
     * @return mixed
     */
    public function getEntity()
    {
        return $this->entity;
    }

    /**
     * @return mixed
     */
    public function getDocument()
    {
        return $this->entity;
    }
}
