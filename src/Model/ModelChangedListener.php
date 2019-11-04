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

use Doctrine\Common\EventArgs;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;

/**
 * Class ModelChangedListener.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class ModelChangedListener implements ModelChangedListenerInterface
{
    /**
     * @var TokenStorage
     */
    protected $tokenStorage;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * @param Logger $logger
     */
    public function __construct(Logger $logger)
    {
        $this->logger = $logger;
    }

    public function setTokenStorage(TokenStorage $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @return TokenStorage
     */
    public function getTokenStorage()
    {
        return $this->tokenStorage;
    }

    /**
     * @param EventArgs $args
     */
    public function postPersist(EventArgs $args)
    {
        $this->getLoggingInfo($args, 'persisted');
    }

    /**
     * @param EventArgs $args
     */
    public function postUpdate(EventArgs $args)
    {
        $this->getLoggingInfo($args, 'updated');
    }

    /**
     * @param EventArgs $args
     */
    public function preRemove(EventArgs $args)
    {
        $this->getLoggingInfo($args, 'removed');
    }
}
