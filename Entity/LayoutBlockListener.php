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

use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Event\LifecycleEventArgs;
use JMS\Serializer\Serializer;
use Networking\InitCmsBundle\Entity\BasePage as Page;
use Networking\InitCmsBundle\Model\ContentInterface;

/**
 * Class LayoutBlockListener
 * @package Networking\InitCmsBundle\Entity
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class LayoutBlockListener
{

    /**
     * @var \JMS\Serializer\Serializer $serializer
     */
    protected $serializer;

    /**
     * @param \JMS\Serializer\Serializer $serializer
     */
    public function __construct(Serializer $serializer)
    {
        $this->serializer = $serializer;
    }

    /**
     * @param \Doctrine\ORM\Event\LifecycleEventArgs $args
     */
    public function postPersist(LifecycleEventArgs $args)
    {
        $layoutBlock = $args->getEntity();
        if ($layoutBlock instanceof LayoutBlock) {
            if ($contentObject = $layoutBlock->getSnapshotContent()) {
                $contentObject = $this->serializer->deserialize($contentObject, $layoutBlock->getClassType(), 'json');
                $em = $args->getEntityManager();

                try {
                    $em->persist($contentObject);
                    $contentObject = $em->merge($contentObject);
                } catch (EntityNotFoundException $e) {
                    $em->detach($contentObject);
                    $classType = $layoutBlock->getClassType();
                    $contentObject = new $classType;
                    $em->persist($contentObject);
                }

                $em->flush($contentObject);

                $layoutBlock->setObjectId($contentObject->getId());

                $em->persist($layoutBlock);
                $em->flush($layoutBlock);

            }
        }
    }

    public function postLoad(LifecycleEventArgs $args)
    {
        $layoutBlock = $args->getEntity();
        if ($layoutBlock instanceof LayoutBlock) {
            if ($layoutBlock->getClassType() || $layoutBlock->getObjectId()) {
                $em = $args->getEntityManager();
                $content = $em->getRepository($layoutBlock->getClassType())->find($layoutBlock->getObjectId());

                if ($content) {
                    $layoutBlock->setContent($content);
                }
            }
        }
    }
}
