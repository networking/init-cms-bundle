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
use Doctrine\ORM\Event\LifecycleEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Networking\InitCmsBundle\Entity\ContentRoute;
use Symfony\Component\DependencyInjection\ContainerAware;


/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class ContentRouteListener extends ContainerAware
{


    /**
     * On PrePersist
     *
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function prePersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof ContentRoute) {

            $templates = $this->container->getParameter('networking_init_cms.page.templates');

            $template = $templates[$entity->getTemplateName()];

            $entity->setTemplate($template['template']);
            $entity->setController($template['controller']);
        }
    }

    /**
     * On PreUpdate
     *
     * @param PreUpdateEventArgs $args
     */
    public function preUpdate(PreUpdateEventArgs $args)
    {
        $entity = $args->getEntity();
        $em = $args->getEntityManager();
        $uow = $em->getUnitOfWork();

        if ($entity instanceof ContentRoute) {

            $templates = $this->container->getParameter('networking_init_cms.page.templates');

            $template = $templates[$entity->getTemplateName()];
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
