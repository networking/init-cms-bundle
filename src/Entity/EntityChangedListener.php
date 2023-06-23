<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Networking\InitCmsBundle\Entity;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsDoctrineListener;
use Doctrine\Common\EventArgs;
use Doctrine\ORM\Event\PreRemoveEventArgs;
use Doctrine\ORM\Events;
use Networking\InitCmsBundle\Model\ModelChangedListener;
use Networking\InitCmsBundle\Model\ModelChangedSubscriber;
use Symfony\Component\DependencyInjection\Attribute\When;

#[AsDoctrineListener(event: Events::preRemove)]
#[AsDoctrineListener(event: Events::postPersist)]
#[AsDoctrineListener(event: Events::postUpdate)]
class EntityChangedListener extends ModelChangedListener
{
    /**
     * @param EventArgs $args
     */
    public function preRemove(PreRemoveEventArgs $args): void
    {
        if(!$this->loggingActive){
            return;
        }
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
    public function getLoggingInfo(EventArgs $args, $method = ''): void
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
            ['username' => $username, 'class' => $entity::class, 'id' => $id]
        );
    }
}
