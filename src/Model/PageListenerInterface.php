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

use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;

/**
 * Class PageListenerInterface.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
interface PageListenerInterface
{
    /**
     * @param LifecycleEventArgs $args
     *
     * @return void
     */
    public function postPersist(PostPersistEventArgs $args): void;

    /**
     * @param OnFlushEventArgs $args
     *
     * @return void
     */
    public function onFlush(OnFlushEventArgs $args): void;
}
