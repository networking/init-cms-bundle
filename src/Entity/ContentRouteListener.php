<?php
/**
 * This file is part of the fksz package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Entity;

use Doctrine\Persistence\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Networking\InitCmsBundle\Model\ContentRoute;
use Networking\InitCmsBundle\Model\ContentRouteListener as ModelContentRouteListener;

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
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();

        if ($entity instanceof ContentRoute) {
            $template = $this->templates[$entity->getTemplateName()];

            $entity->setTemplate($template['template']);
            $entity->setController($template['controller']);
        }
    }

    /**
     * @param PreUpdateEventArgs $args
     *
     * @return mixed|void
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {

        $entity = $args->getObject();

        if ($entity instanceof ContentRoute) {
            if ($args->hasChangedField('templateName')) {
                $templateName = $args->getNewValue('templateName');
                if (array_key_exists($templateName, $this->templates)) {
                    $template = $this->templates[$templateName];
                    $entity->setTemplate($template['template']);
                    $entity->setTemplateName($templateName);
                    $entity->setController($template['controller']);
                }
            }
        }
    }
}
