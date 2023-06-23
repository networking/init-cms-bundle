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

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Networking\InitCmsBundle\Model\ContentRouteInterface;
use Networking\InitCmsBundle\Model\ContentRouteListener as ModelContentRouteListener;

#[AsEntityListener(event: Events::prePersist, method: 'prePersist', entity: ContentRoute::class)]
#[AsEntityListener(event: Events::preUpdate, method: 'preUpdate', entity: ContentRoute::class)]
/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class ContentRouteListener extends ModelContentRouteListener
{
    /**
     * @param LifecycleEventArgs $args
     *
     * @return mixed|void
     */
    public function prePersist(
        ContentRouteInterface $contentRoute,
        PrePersistEventArgs $args
    ): void {
        $template = $this->templates[$contentRoute->getTemplateName()];

        $contentRoute->setTemplate($template['template']);
        $contentRoute->setController($template['controller']);
    }

    /**
     * @param PreUpdateEventArgs $args
     *
     * @return mixed|void
     */
    public function preUpdate(
        ContentRouteInterface $contentRoute,
        PreUpdateEventArgs $args
    ): void {
        if ($args->hasChangedField('templateName')) {
            $templateName = $args->getNewValue('templateName');
            if (array_key_exists($templateName, $this->templates)) {
                $template = $this->templates[$templateName];
                $contentRoute->setTemplate($template['template']);
                $contentRoute->setTemplateName($templateName);
                $contentRoute->setController($template['controller']);
            }
        }
    }
}
