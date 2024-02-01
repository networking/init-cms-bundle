<?php

declare(strict_types=1);

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Entity;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Networking\InitCmsBundle\Component\EventDispatcher\CmsEvent;
use Networking\InitCmsBundle\Helper\BundleGuesser;
use Networking\InitCmsBundle\Model\MenuItemInterface;
use Networking\InitCmsBundle\Model\PageInterface;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Class LastEditedListener.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
class LastEditedListener
{
    /**
     * @var RequestStack
     */
    protected $requestStack;

    /**
     * @var BundleGuesser
     */
    protected $bundleGuesser;

    /**
     * LastEditedListener constructor.
     */
    public function __construct(RequestStack $requestStack)
    {
        $this->requestStack = $requestStack;
        $this->bundleGuesser = new BundleGuesser();
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(PostPersistEventArgs $args): void
    {
        $this->setSessionVariable($args->getObject());
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $this->setSessionVariable($args->getObject());
    }

    /**
     * @return mixed|void
     */
    #[AsEventListener]
    public function registerEdited(CmsEvent $event): void
    {
        $this->setSessionVariable($event->getEntity());
    }

    protected function setSessionVariable($entity): void
    {
        if (!$this->requestStack->getMainRequest()) {
            return;
        }

        if (!$this->requestStack->getMainRequest()->hasSession()) {
            return;
        }

        $this->bundleGuesser->initialize($entity);

        $name = $this->bundleGuesser->getShortName();

        if ($entity instanceof MenuItemInterface) {
            $name = 'MenuItem';
        }

        if ($entity instanceof PageInterface) {
            $name = 'Page';
        }

        try {
            $this->requestStack->getSession()->set($name.'.last_edited', $entity->getId());
        } catch (SessionNotFoundException) {
            return;
        }
    }
}
