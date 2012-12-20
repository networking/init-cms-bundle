<?php
/*
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\EventListener;
use Doctrine\ORM\Event\LifecycleEventArgs,
    Networking\InitCmsBundle\Entity\Page,
    Networking\InitCmsBundle\Entity\LayoutBlock;
/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class LayoutBlockListener
{

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $this->autoPageDraft($args);
    }

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postUpdate(LifecycleEventArgs $args)
    {
        $this->autoPageDraft($args);
    }

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function autoPageDraft(LifecycleEventArgs $args)
    {
        $layoutBlock = $args->getEntity();
        if($layoutBlock instanceof LayoutBlock){

            $page = $layoutBlock->getPage();
            $page->setStatus(Page::STATUS_DRAFT);
            $em = $args->getEntityManager();
            $em->persist($page);
            $em->flush();
        }
    }
}
