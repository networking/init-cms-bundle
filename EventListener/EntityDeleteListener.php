<?php

namespace Networking\InitCmsBundle\EventListener;

use Doctrine\ORM\Event\LifecycleEventArgs;

class EntityDeleteListener
{



    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {

        if(method_exists($args->getEntity(), 'isDeletable'))
        {
            if($args->getEntity()->isDeletable() == 0)
            {
               //find a solution... like throwing super Exception thingy
            }
        }


    }



}