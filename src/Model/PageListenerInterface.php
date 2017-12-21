<?php
/**
 * This file is part of the init_cms_sandbox package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Model;

use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\OnFlushEventArgs;

/**
 * Class PageListenerInterface
 * @package Networking\InitCmsBundle\Model
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
interface PageListenerInterface {

    /**
     * @param LifecycleEventArgs $args
     * @return mixed
     */
    public function postPersist(LifecycleEventArgs $args);

    /**
     * @param OnFlushEventArgs $args
     * @return mixed
     */
    public function onFlush(OnFlushEventArgs $args);
}
 