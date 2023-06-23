<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Networking\InitCmsBundle\Model;

use Doctrine\Common\EventArgs;

/**
 * Class ModelChangedListenerInterface.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
interface ModelChangedListenerInterface
{
    /**
     * @param EventArgs $args
     * @param string    $method
     *
     * @return mixed
     */
    public function getLoggingInfo(EventArgs $args, $method = ''): void;
}
