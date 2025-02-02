<?php

declare(strict_types=1);

/**
 * This file is part of the init_cms_sandbox package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Model;

use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;

/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
interface ContentRouteListenerInterface
{
    /**
     * @param LifecycleEventArgs $args
     *
     * @return mixed
     */
    public function prePersist(ContentRouteInterface $contentRoute, PrePersistEventArgs $args): void;

    /**
     * @return mixed
     */
    public function preUpdate(ContentRouteInterface $contentRoute, PreUpdateEventArgs $args): void;
}
