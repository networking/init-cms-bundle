<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Component\EventDispatcher;

use Symfony\Component\EventDispatcher\Event;

/**
 * Class CmsEvent
 * @package Networking\InitCmsBundle\Component\EventDispatcher
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class CmsEvent extends Event
{
    /**
     * @var $entity
     */
    protected $entity;

    /**
     * @param $entity
     */
    public function __construct($entity)
    {
        $this->entity = $entity;
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
