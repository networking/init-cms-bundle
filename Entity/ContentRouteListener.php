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
use Doctrine\Common\Persistence\Event\LifecycleEventArgs;
use Networking\InitCmsBundle\Model\ContentRoute;
use Networking\InitCmsBundle\Model\ContentRouteListener as ModelContentRouteListener;

/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class ContentRouteListener extends ModelContentRouteListener
{
    /**
     * @param LifecycleEventArgs $args
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
     * @param LifecycleEventArgs $args
     * @return mixed|void
     */
    public function preUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getObject();
        $em = $args->getObjectManager();
        $uow = $em->getUnitOfWork();

        if ($entity instanceof ContentRoute) {

            $template = $this->templates[$entity->getTemplateName()];
            $changeset = $uow->getEntityChangeSet($entity);
            if (isset($changeset['templateName']) && $args->hasChangedField('templateName')) {
                $entity->setTemplate($template['template']);
                $entity->setController($template['controller']);

                $uow->recomputeSingleEntityChangeSet(
                    $em->getClassMetadata("NetworkingInitCmsBundle:ContentRoute"),
                    $entity
                );
            }

        }
    }

}
