<?php
/**
 * This file is part of the fksz package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Document;

use Doctrine\Common\EventArgs;
use Networking\InitCmsBundle\Model\ContentRoute;
use Networking\InitCmsBundle\Model\ContentRouteListener as ModelContentRouteListener;

/**
 * Class ContentRouteListener
 * @package Networking\InitCmsBundle\Document
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class ContentRouteListener extends ModelContentRouteListener
{


    /**
     * @param EventArgs $args
     * @return mixed|void
     */
    public function prePersist(EventArgs $args)
    {
        $entity = $args->getDocument();

        if ($entity instanceof ContentRoute) {


            $template = $this->templates[$entity->getTemplateName()];

            $entity->setTemplate($template['template']);
            $entity->setController($template['controller']);
        }
    }

    /**
     * @param EventArgs $args
     * @return mixed|void
     */
    public function preUpdate(EventArgs $args)
    {
        $entity = $args->getDocument();
        $em = $args->getDocumentManger();
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
