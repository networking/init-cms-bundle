<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\EventListener;

use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Networking\InitCmsBundle\Model\UserInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class UserActivityListener
 * @package Networking\InitCmsBundle\EventListener
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class UserActivityListener implements ContainerAwareInterface
{
    /**
     * @var SecurityContext
     */
    protected $context;

    /**
     * @var ObjectManager
     */
    protected $em;

    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
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
       $this->setSecurityContext($container->get('security.context'));
    }

    public function setSecurityContext(SecurityContext $context)
    {
        $this->context = $context;

    }

    /**
     * On each request we want to update the user's last activity datetime
     *
     * @param \Symfony\Component\HttpKernel\Event\FilterControllerEvent $event
     * @return void
     */
    public function onCoreController(FilterControllerEvent $event)
    {
        if (!$this->context->getToken()) {
            return;
        }
        $user = $this->context->getToken()->getUser();
        if ($user instanceof UserInterface) {

            //here we can update the user as necessary
            if (method_exists($user, 'setLastActivity')) {
                try{
                    $user->setLastActivity(new \DateTime('now'));
                    $this->em->persist($user);
                    $this->em->flush($user);
                }catch(\Doctrine\ORM\ORMException $e){
                    //do nothing, entity manager is closed
                }

            }
        }
    }
}
