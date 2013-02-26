<?php

namespace Networking\InitCmsBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs,
    Symfony\Component\DependencyInjection\Container,
    Symfony\Bridge\Monolog\Logger;

class EntityChangedListener
{

    protected $logger;
    protected $container;

    /**
     * @param \Symfony\Component\DependencyInjection\Container $container
     * @param \Symfony\Bridge\Monolog\Logger $logger
     */
    public function __construct(Container $container, Logger $logger)
    {
        $this->container = $container;
        $this->logger = $logger;
    }

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->getLoggingInfo($args, 'persisted');
    }

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->getLoggingInfo($args, 'updated');
    }

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {
        $this->getLoggingInfo($args, 'removed');
    }

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     * @param string $method
     */
    protected function getLoggingInfo(LifecycleEventArgs $args, $method = '')
    {
        $entity = $args->getEntity();
        $security = $this->container->get('security.context');
        if ($security->getToken() && $security->getToken()->getUser() != 'anon.') {

            $username = $security->getToken()->getUser()->getUsername();

        } else {
            $username = 'doctrine:fixtures:load!';
        }
        $this->logger->info(
            sprintf('entity %s', $method),
            array('username' => $username, 'class' => get_class($entity), 'id' => $entity->getId())
        );

    }

}