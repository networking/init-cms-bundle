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

namespace Networking\InitCmsBundle\Entity;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\Common\EventArgs;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;
use Networking\InitCmsBundle\Model\ModelChangedListenerInterface;
use Networking\InitCmsBundle\Enitty\EntityChangedSubscriber;
use Symfony\Bridge\Monolog\Logger;
use Symfony\Component\DependencyInjection\Attribute\When;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

#[AsDoctrineListener(event: Events::preRemove)]
#[AsDoctrineListener(event: Events::postPersist)]
    #[AsDoctrineListener(event: Events::postUpdate)]
class EntityChangedListener implements ModelChangedListenerInterface
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

        if (method_exists($args->getEntity(), 'isDeletable')) {
            if ($args->getEntity()->isDeletable() == 0) {
                //find a solution... like throwing super Exception thingy
            }
        }
    }
    

    /**
     * @param EventArgs $args
     * @param string    $method
     *
     * @return mixed|void
     */
    public function getLoggingInfo(EventArgs $args, $method = ''): void
    {

        $entity = $args->getEntity();

        if ($this->getTokenStorage()->getToken() && $this->getTokenStorage()->getToken()->getUser() != 'anon.') {
            $username = $this->getTokenStorage()->getToken()->getUser()->getUsername();
        } else {
            $username = 'doctrine:fixtures:load!';
        }
        $id = method_exists($entity, 'getId') ? $entity->getId() : null;
        $this->logger->info(
            sprintf('entity %s', $method),
            ['username' => $username, 'class' => $entity::class, 'id' => $id]
        );
    }
}
