<?php

declare(strict_types=1);

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityNotFoundException;
use Doctrine\ORM\Event\LifecycleEventArgs;
use JMS\Serializer\Serializer;
use Networking\InitCmsBundle\Model\ContentInterface;

/**
 * Class LayoutBlockListener.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class LayoutBlockListener
{

    public function postLoad(LifecycleEventArgs $args)
    {
        $layoutBlock = $args->getEntity();
        if ($layoutBlock instanceof LayoutBlock) {
            if ($layoutBlock->getClassType() || $layoutBlock->getObjectId()) {
                /** @var EntityManager $em */
                $em = $args->getObjectManager();
                if ($layoutBlock->getObjectId()) {
                    /** @var ContentInterface $content */
                    $content = $em->getRepository($layoutBlock->getClassType())->find($layoutBlock->getObjectId());
                    if ($content) {
                        $layoutBlock->setContent($content);
                    }
                } else {
                    $em->remove($layoutBlock);
                    $em->flush();
                }
            }
        }
    }
}
