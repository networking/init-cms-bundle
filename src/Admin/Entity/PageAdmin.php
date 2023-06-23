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

namespace Networking\InitCmsBundle\Admin\Entity;

use Gedmo\Loggable\Entity\LogEntry;
use Networking\InitCmsBundle\Admin\Model\PageAdmin as BasePageAdmin;
use Networking\InitCmsBundle\Entity\LayoutBlock;
use Networking\InitCmsBundle\Model\PageInterface;

/**
 * Class PageAdmin.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class PageAdmin extends BasePageAdmin
{
    private ?LogEntry $lastEditedBy = null;


    public function getPageByLayoutBlock(LayoutBlock $layoutBlock): PageInterface
    {
        return $layoutBlock->getPage();
    }

    public function getLastEditedBy(): ?LogEntry
    {
        if ($this->lastEditedBy) {
            return $this->lastEditedBy;
        }

        $loggableClass = LogEntry::class;
        /** @var \Gedmo\Loggable\Entity\Repository\LogEntryRepository $repo */
        $repo = $this->getModelManager()->getEntityManager($loggableClass)
            ->getRepository($loggableClass);

        $this->lastEditedBy = $repo->findOneBy([
            'objectClass' => $this->getClass(),
            'objectId' => $this->getSubject()->getId(),
        ], ['version' => 'DESC']);

        return $this->lastEditedBy;
    }
}
