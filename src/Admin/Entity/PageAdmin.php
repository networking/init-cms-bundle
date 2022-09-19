<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Admin\Entity;

use Networking\InitCmsBundle\Admin\Model\PageAdmin as ModelPageAdmin;
use Networking\InitCmsBundle\Entity\LayoutBlock;

/**
 * Class PageAdmin.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class PageAdmin extends ModelPageAdmin
{
    /**
     * @param LayoutBlock $layoutBlock
     *
     * @return \Networking\InitCmsBundle\Model\PageInterface
     */
    public function getPageByLayoutBlock(LayoutBlock $layoutBlock)
    {
        return $layoutBlock->getPage();
    }

    /**
     * @return mixed
     */
    public function getLastEditedBy()
    {
        $loggableClass = 'Gedmo\\Loggable\\Entity\\LogEntry';
        /** @var \Gedmo\Loggable\Entity\Repository\LogEntryRepository $repo */
        $repo = $this->getModelManager()->getEntityManager($this->getClass())->getRepository($loggableClass);
        $logEntries = $repo->getLogEntries($this->getSubject());

        return array_shift($logEntries);
    }
}
