<?php

namespace Networking\InitCmsBundle\Entity;

use Doctrine\Common\EventArgs;
use Networking\InitCmsBundle\Model\ModelChangedSubscriber;

class EntityChangedSubscriber extends ModelChangedSubscriber
{
    /**
     * @param EventArgs $args
     */
    public function preRemove(EventArgs $args)
    {
        parent::preRemove($args);
        if (method_exists($args->getEntity(), 'isDeletable')) {
            if ($args->getEntity()->isDeletable() == 0) {
                //find a solution... like throwing super Exception thingy
            }
        }
    }

    /**
     * @param EventArgs $args
     * @param string    $method
     *
     * @return mixed|void
     */
    public function getLoggingInfo(EventArgs $args, $method = '')
    {
        $entity = $args->getEntity();

        if ($this->getTokenStorage()->getToken() && $this->getTokenStorage()->getToken()->getUser() != 'anon.') {
            $username = $this->getTokenStorage()->getToken()->getUser()->getUsername();
        } else {
            $username = 'doctrine:fixtures:load!';
        }
        $id = method_exists($entity, 'getId') ? $entity->getId() : null;
        $this->logger->info(
            sprintf('entity %s', $method),
            ['username' => $username, 'class' => get_class($entity), 'id' => $id]
        );
    }
}
