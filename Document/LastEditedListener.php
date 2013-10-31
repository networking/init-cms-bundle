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

use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;
use Networking\InitCmsBundle\Component\EventDispatcher\CmsEvent;
use Networking\InitCmsBundle\Document\BasePage as Page;
use Networking\InitCmsBundle\Model\LastEditedListener as ModelLastEditedListener;

/**
 * Class LastEditedListener
 * @package Networking\InitCmsBundle\Document
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class LastEditedListener extends ModelLastEditedListener
{

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
        if ($entity instanceof BaseMenuItem || $entity instanceof Page) {

            $this->bundleGuesser->initialize($entity);

            $name = $this->bundleGuesser->getShortName();

            $this->session->set($name . '.last_edited', $entity->getId());
        }
    }
}
