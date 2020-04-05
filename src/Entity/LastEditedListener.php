<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Entity;

use Doctrine\ORM\Event\LifecycleEventArgs;
use Networking\InitCmsBundle\Component\EventDispatcher\CmsEvent;
use Networking\InitCmsBundle\Entity\BasePage as Page;
use Networking\InitCmsBundle\Model\LastEditedListener as ModelLastEditedListener;
use Networking\InitCmsBundle\Model\MenuItemInterface;
use Networking\InitCmsBundle\Model\PageInterface;

/**
 * Class LastEditedListener.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class LastEditedListener extends ModelLastEditedListener
{
    /**
     * @param LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->setSessionVariable($args->getEntity());
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->setSessionVariable($args->getEntity());
    }

    /**
     * @param CmsEvent $event
     * @return mixed|void
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
        $this->bundleGuesser->initialize($entity);

        $name = $this->bundleGuesser->getShortName();

        if ($entity instanceof MenuItemInterface) {
            $name = 'MenuItem';
        }

        if($entity instanceof PageInterface){
            $name = 'Page';
        }
        
        $this->session->set($name.'.last_edited', $entity->getId());
    }
}
