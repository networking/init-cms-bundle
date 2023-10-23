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
namespace Networking\InitCmsBundle\EventListener;

use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\ORM\Event\PostPersistEventArgs;
use Doctrine\ORM\Event\PostRemoveEventArgs;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Networking\InitCmsBundle\Cache\PageCacheInterface;
use Networking\InitCmsBundle\Entity\MenuItem;

#[
    AsEntityListener(event: Events::postPersist, method: 'postPersist', entity: MenuItem::class),
    AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: MenuItem::class),
    AsEntityListener(event: Events::postRemove, method: 'postRemove', entity: MenuItem::class),
]
class CacheCleaner
{
    /**
     * @var int
     */
    protected $cleanCount = 0;
    /**
     * @var PageCacheInterface
     */
    protected $pageCache;

    public function __construct(PageCacheInterface $pageCache)
    {
        $this->pageCache = $pageCache;
    }

    public function postPersist(MenuItem $menuItem, PostPersistEventArgs $args)
    {
            $this->cleanCache();
    }

    public function postUpdate(MenuItem $menuItem, PostUpdateEventArgs $args)
    {
            $this->cleanCache();
    }

    public function postRemove(MenuItem $menuItem, PostRemoveEventArgs $args)
    {
            $this->cleanCache();
    }

    /**
     * remove items from the cache and stop after one item.
     */
    protected function cleanCache()
    {
        if ($this->cleanCount < 1) {
            ++$this->cleanCount;
            if (is_object($this->pageCache)) {
                $this->pageCache->clean();
            }
        }
    }
}
