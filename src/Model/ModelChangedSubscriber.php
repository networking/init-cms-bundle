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

use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\Common\EventArgs;
use Doctrine\ORM\Events;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class ModelChangedListener.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class ModelChangedSubscriber implements ModelChangedSubscriberInterface, EventSubscriberInterface
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
     * @var bool
     */
    protected $loggingActive;

    /**
     * ModelChangedListener constructor.
     * @param Logger $logger
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(Logger $logger, TokenStorageInterface $tokenStorage, $loggingActive = false)
    {
        $this->logger = $logger;
        $this->tokenStorage = $tokenStorage;
        $this->loggingActive = $loggingActive;

    }

    public function getSubscribedEvents()
    {
        if(!$this->loggingActive){
            return [];
        }

        return [
            Events::postPersist,
            Events::postUpdate,
            Events::postRemove,
        ];
    }

    /**
     * @return TokenStorageInterface
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
