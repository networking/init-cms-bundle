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
     * @return
     */
    public function getEntity()
    {
        return $this->entity;
    }

    public function getDocument()
    {
        return $this->entity;
    }
}
