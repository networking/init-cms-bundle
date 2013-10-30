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
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\Security\Core\SecurityContext;

/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class ModelChangedListener implements ModelChangedListenerInterface, ContainerAwareInterface
{

    /**
     * @var ContainerInterface
     */
    protected $container;
    /**
     * @var SecurityContext
     */
    protected $securityContext;

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

    /**
     * Sets the Container.
     *
     * @param ContainerInterface|null $container A ContainerInterface instance or null
     *
     * @api
     */
    public function setContainer(ContainerInterface $container = null)
    {
        $this->container = $container;
    }

    /**
     * @return object|SecurityContext
     */
    public function getSecurityContext()
    {
        if(!$this->securityContext){
            $this->securityContext =  $this->container->get('security.context');
        }
        return $this->securityContext;
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
 