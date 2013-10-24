<?php

namespace Networking\InitCmsBundle\Entity;

use Doctrine\ODM\MongoDB\Event\LifecycleEventArgs;

class EntityDeleteListener
{

    /**
     * @param \Doctrine\ODM\MongoDB\Event\LifecycleEventArgs $args
     */
    public function preRemove(LifecycleEventArgs $args)
    {

        if(method_exists($args->getDocument(), 'isDeletable'))
        {
            if($args->getDocument()->isDeletable() == 0)
            {
               //find a solution... like throwing super Exception thingy
            }
        }
    }
}