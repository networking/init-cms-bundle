<?php

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Entity;

use Doctrine\Common\EventArgs;
use Networking\InitCmsBundle\Model\Page;
use Networking\InitCmsBundle\Model\PageInterface;
use Networking\InitCmsBundle\Helper\PageHelper;
use Networking\InitCmsBundle\Model\PageListener as ModelPageListener;

/**
 * Class PageListener
 * @package Networking\InitCmsBundle\Entity
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class PageListener extends ModelPageListener
{

    /**
     * @param EventArgs $args
     * @return mixed|void
     */
    public function postPersist(EventArgs $args)
    {
        $entity = $args->getEntity();

        $em = $args->getEntityManager();

        if ($entity instanceof PageInterface) {

            if ($contentRoute = $entity->getContentRoute()) {

                $contentRoute->setObjectId($entity->getId());
                $contentRoute->setPath(PageHelper::getPageRoutePath($entity->getPath()));

                $em->persist($contentRoute);
                $em->getUnitOfWork()->computeChangeSet($em->getClassMetadata(get_class($contentRoute)), $contentRoute);
            }
        }
    }

    /**
     * @param EventArgs $args
     * @return mixed|void
     */
    public function onFlush(EventArgs $args)
    {
        $em = $args->getEntityManager();
        $unitOfWork = $em->getUnitOfWork();

        foreach ($unitOfWork->getScheduledEntityUpdates() as $entity) {

            if ($entity instanceof PageInterface) {
                if ($contentRoute = $entity->getContentRoute()) {
                    $contentRoute->setPath(PageHelper::getPageRoutePath($entity->getPath()));
                    $em->persist($contentRoute);
                    $unitOfWork->computeChangeSet($em->getClassMetadata(get_class($contentRoute)), $contentRoute);
                    foreach ($entity->getAllChildren() as $child) {
                        $contentRoute = $child->getContentRoute();
                        $contentRoute->setPath(PageHelper::getPageRoutePath($child->getPath()));
                        $em->persist($contentRoute);
                        $unitOfWork->computeChangeSet($em->getClassMetadata(get_class($contentRoute)), $contentRoute);

                        if ($entity->getStatus() == Page::STATUS_PUBLISHED) {
                            if ($childSnapshot = $child->getSnapshot()) {
                                $snapshotRoute = $childSnapshot->getContentRoute();

                                $newPath = PageHelper::getPageRoutePath($child->getPath());

                                $snapshotRoute->setPath($newPath);
                                $childSnapshot->setPath($newPath);

                                $em->persist($childSnapshot);
                                $em->persist($snapshotRoute);

                                $unitOfWork->computeChangeSet(
                                    $em->getClassMetadata(get_class($childSnapshot)),
                                    $childSnapshot
                                );
                                $unitOfWork->computeChangeSet(
                                    $em->getClassMetadata(get_class($snapshotRoute)),
                                    $snapshotRoute
                                );

                            }
                        }
                    }
                }
            }
        }


    }
}
