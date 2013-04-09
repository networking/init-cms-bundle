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


/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
use Symfony\Component\Security\Core\SecurityContext;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use DateTime;
use Sonata\UserBundle\Model\UserInterface;

class UserActivity
{
    protected $context;
    protected $em;

    public function __construct(SecurityContext $context, $em)
    {
        $this->context = $context;
        $this->em = $em;
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
                $user->setLastActivity(new DateTime());
                $this->em->persist($user);
                $this->em->flush($user);
            }
        }
    }
}
