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

use Symfony\Component\EventDispatcher\EventSubscriberInterface,
    Doctrine\ORM\Event\LifecycleEventArgs,
    Symfony\Component\HttpFoundation\Session\Session,
    Networking\InitCmsBundle\Helper\BundleGuesser,
    Networking\InitCmsBundle\Component\EventDispatcher\CmsEvent,
    Networking\InitCmsBundle\Entity\Page,
    Networking\InitCmsBundle\Entity\MenuItem;

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
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->setSessionVariable($args->getEntity());
    }

    /**
     * On Menu Update
     *
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->setSessionVariable($args->getEntity());
    }

    /**
     * @param \Networking\InitCmsBundle\Component\EventDispatcher\CmsEvent $event
     */
    public function registerEdited(CmsEvent $event)
    {
        $this->setSessionVariable($event->getEntity());
    }

    /**
     * @param $entity
     */
    protected function setSessionVariable($entity)
    {
        if ($entity instanceof MenuItem || $entity instanceof Page) {

            $this->bundleGuesser->initialize($entity);

            $name = $this->bundleGuesser->getShortName();

            $this->session->set($name . '.last_edited', $entity->getId());
        }
    }
}
