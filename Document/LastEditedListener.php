<?php

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Document;

use Networking\InitCmsBundle\Model\MenuItemInterface;
use Networking\InitCmsBundle\Model\PageInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Symfony\Component\HttpFoundation\Session\Session;
use Networking\InitCmsBundle\Helper\BundleGuesser;
use Networking\InitCmsBundle\Component\EventDispatcher\CmsEvent;

/**
 * @author net working AG <info@networking.ch>
 */
class LastEditedListener implements EventSubscriberInterface
{

    /**
     * @var Session $session
     */
    protected $session;

    /**
     * @var BundleGuesser $bundleGuesser
     */
    protected $bundleGuesser;

    /**
     * @param \Symfony\Component\HttpFoundation\Session\Session $session
     */
    public function __construct(Session $session)
    {
        $this->session = $session;
        $this->bundleGuesser = new BundleGuesser();

    }

    /**
     * @return array|void
     */
    public static function getSubscribedEvents()
    {
        return array(
            'crud_controller.edit_entity' => 'registerEdited',
        );
    }

    /**
     * On Menu Create
     *
     * @param \Doctrine\ODM\MongoDB\Event\LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->setSessionVariable($args->getDocument());
    }

    /**
     * On Menu Update
     *
     * @param \Doctrine\ODM\MongoDB\Event\LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->setSessionVariable($args->getDocument());
    }

    /**
     * @param \Networking\InitCmsBundle\Component\EventDispatcher\CmsEvent $event
     */
    public function registerEdited(CmsEvent $event)
    {
        $this->setSessionVariable($event->getDocument());
    }

    /**
     * @param $entity
     */
    protected function setSessionVariable($entity)
    {
        if ($entity instanceof MenuItemInterface || $entity instanceof PageInterface) {

            $this->bundleGuesser->initialize($entity);

            $name = $this->bundleGuesser->getShortName();

            $this->session->set($name . '.last_edited', $entity->getId());
        }
    }
}
