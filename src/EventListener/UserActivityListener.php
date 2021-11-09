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

use Symfony\Component\HttpKernel\Event\ControllerEvent;
use Networking\InitCmsBundle\Model\UserInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Class UserActivityListener.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class UserActivityListener
{
    /**
     * @var TokenStorageInterface
     */
    protected $tokenStorage;

    /**
     * @var EntityManagerInterface
     */
    protected $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    /**
     * @param TokenStorageInterface $tokenStorage
     */
    public function setTokenStorage(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * @param ControllerEvent $event
     * @throws \Exception
     */
    public function onCoreController(ControllerEvent $event)
    {
        // do not capture admin cms urls
        if (!preg_match('/.*\/admin\/.*/', $event->getRequest()->getRequestUri())) {
            return;
        }

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
                    $this->em->flush();
                } catch (\Doctrine\ORM\ORMException $e) {
                    //do nothing, entity manager is closed
                }
            }
        }
    }
}
