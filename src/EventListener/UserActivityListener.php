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

use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorage;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Networking\InitCmsBundle\Model\UserInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class UserActivityListener.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class UserActivityListener
{
    /**
     * @var TokenStorage
     */
    protected $tokenStorage;

    /**
     * @var ObjectManager
     */
    protected $em;

    public function __construct(ObjectManager $em)
    {
        $this->em = $em;
    }

    /**
     * @param TokenStorage $tokenStorage
     */
    public function setTokenStorage(TokenStorage $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * On each request we want to update the user's last activity datetime.
     *
     * @param \Symfony\Component\HttpKernel\Event\FilterControllerEvent $event
     */
    public function onCoreController(FilterControllerEvent $event)
    {
        if (!$this->tokenStorage->getToken()) {
            return;
        }
        $user = $this->tokenStorage->getToken()->getUser();
        if ($user instanceof UserInterface) {

            //here we can update the user as necessary
            if (method_exists($user, 'setLastActivity')) {
                try {
                    $user->setLastActivity(new \DateTime('now'));
                    $this->em->persist($user);
                    $this->em->flush($user);
                } catch (\Doctrine\ORM\ORMException $e) {
                    //do nothing, entity manager is closed
                }
            }
        }
    }
}
