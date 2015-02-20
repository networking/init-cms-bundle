<?php

namespace Networking\InitCmsBundle\Entity;

use Doctrine\Common\EventArgs;
use Networking\InitCmsBundle\Model\ModelChangedListener;

class EntityChangedListener extends ModelChangedListener
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
     * @param string $method
     * @return mixed|void
     */
    public function getLoggingInfo(EventArgs $args, $method = '')
    {
        $entity = $args->getEntity();
        if ($this->getSecurityContext()->getToken() && $this->getSecurityContext()->getToken()->getUser() != 'anon.') {

            $username = $this->getSecurityContext()->getToken()->getUser()->getUsername();

        } else {
            $username = 'doctrine:fixtures:load!';
        }
        $this->logger->info(
            sprintf('entity %s', $method),
            array('username' => $username, 'class' => get_class($entity), 'id' => $entity->getId())
        );

    }

}