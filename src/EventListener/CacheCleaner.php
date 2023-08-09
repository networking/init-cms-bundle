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

use Doctrine\ORM\Event\LifecycleEventArgs;
use Networking\InitCmsBundle\Cache\PageCacheInterface;
use Networking\InitCmsBundle\Entity\MenuItem;

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

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof MenuItem) {
            $this->cleanCache();
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof MenuItem) {
            $this->cleanCache();
        }
    }

    public function postRemove(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        if ($entity instanceof MenuItem) {
            $this->cleanCache();
        }
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
