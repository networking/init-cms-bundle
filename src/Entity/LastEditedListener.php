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
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Networking\InitCmsBundle\Component\EventDispatcher\CmsEvent;
use Networking\InitCmsBundle\Entity\BasePage as Page;
use Networking\InitCmsBundle\Model\LastEditedListener as ModelLastEditedListener;
use Networking\InitCmsBundle\Model\MenuItemInterface;
use Networking\InitCmsBundle\Model\PageInterface;
use Symfony\Component\HttpFoundation\Exception\SessionNotFoundException;


#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
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
    public function postPersist(PostPersistEventArgs $args): void
    {
        $this->setSessionVariable($args->getEntity());
    }

    /**
     * @param LifecycleEventArgs $args
     */
    public function postUpdate(PostUpdateEventArgs $args): void
    {
        $this->setSessionVariable($args->getEntity());
    }

    /**
     * @return mixed|void
     */
    public function registerEdited(CmsEvent $event): void
    {
        $this->setSessionVariable($event->getEntity());
    }

    /**
     * @param $entity
     */
    protected function setSessionVariable($entity): void
    {

        if(!$this->requestStack->getMainRequest()){
            return;
        }

        if(!$this->requestStack->getMainRequest()->hasSession()){
            return;
        }

        $this->bundleGuesser->initialize($entity);

        $name = $this->bundleGuesser->getShortName();

        if ($entity instanceof MenuItemInterface) {
            $name = 'MenuItem';
        }

        if($entity instanceof PageInterface){
            $name = 'Page';
        }
        
        try{
            $this->requestStack->getSession()->set($name.'.last_edited', $entity->getId());
        }catch (SessionNotFoundException){
            return;
        }
    }
}
