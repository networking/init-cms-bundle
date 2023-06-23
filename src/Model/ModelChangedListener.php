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
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class ModelChangedListener.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class ModelChangedListener implements ModelChangedListenerInterface
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var Logger
     */
    protected $logger;

    /**
     * ModelChangedListener constructor.
     * @param bool $loggingActive
     */
    public function __construct(Logger $logger, TokenStorageInterface $tokenStorage, protected $loggingActive = false)
    {
        $this->logger = $logger;
        $this->tokenStorage = $tokenStorage;

    }

    /**
     * @return TokenStorageInterface
     */
    public function getTokenStorage(): TokenStorageInterface
    {
        return $this->tokenStorage;
    }

    /**
     * @param EventArgs $args
     */
    public function postPersist(PostPersistEventArgs $args): void
    {
        if(!$this->loggingActive){
            return;
        }
        $this->getLoggingInfo($args, 'persisted');
    }

    /**
     * @param EventArgs $args
     */
    public function postUpdate(PostUpdateEventArgs $args): void
    {
        if(!$this->loggingActive){
            return;
        }
        $this->getLoggingInfo($args, 'updated');
    }

    /**
     * @param EventArgs $args
     */
    public function preRemove(PreRemoveEventArgs $args): void
    {
        if(!$this->loggingActive){
            return;
        }
        $this->getLoggingInfo($args, 'removed');
    }
}
