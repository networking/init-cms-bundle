<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Cache;

use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Cache\Adapter\AdapterInterface;
use Symfony\Component\Cache\CacheItem;
use Symfony\Component\Cache\PruneableInterface;
use Symfony\Component\Cache\ResettableInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class PageCache
 *
 * @package Networking\InitCmsBundle\Cache
 */
class PageCache implements
    PageCacheInterface,
    AdapterInterface,
    PruneableInterface,
    ResettableInterface
{
    /**
     * PageCache constructor.
     *
     * @param int $expiresAfter
     * @param bool $activated
     */
    public function __construct(
        private readonly CacheItemPoolInterface $pageCache,
        private $expiresAfter = 86400,
        private $activated = true
    ) {
    }

    /**
     * {@inheritdoc}
     */
    public function isCacheable(Request $request, $user): bool
    {
        if (!$this->activated) {
            return false;
        }

        if ($user) {
            return false;
        }

        if ($request->hasSession()
            && $request->getSession()->get(
                'no_cache',
                false
            )
        ) {
            $request->getSession()->remove('no_cache');

            return false;
        }

        if ($request->getMethod() != 'GET') {
            return false;
        }

        return true;
    }

    /**
     * {@inheritdoc}
     */
    public function get($keyword)
    {

        /** @var ItemInterface $item */
        $item = $this->getItem(md5((string)$keyword));

        if (!$item->isHit()) {
            return null;
        }

        return $item->get();
    }

    /**
     * {@inheritdoc}
     */
    public function set($keyword, $value = '', $time = null)
    {
        $item = $this->getItem(md5((string)$keyword));

        $item->set($value);

        if (is_null($time)) {
            $time = $this->expiresAfter;
        }
        $item->expiresAfter($time);

        return $this->save($item);
    }

    /**
     * {@inheritdoc}
     */
    public function delete($keyword)
    {
        return $this->pageCache->delete(md5((string)$keyword));
    }

    /**
     * {@inheritdoc}
     *
     * @return CacheItemInterface
     */
    public function getItem($key): \Symfony\Component\Cache\CacheItem
    {
        return $this->pageCache->getItem($key);
    }

    /**
     * {@inheritdoc}
     *
     * @return \Traversable|CacheItem[]
     */
    public function getItems(array $keys = []): iterable
    {
        return $this->pageCache->getItems($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function hasItem($key): bool
    {
        return $this->pageCache->hasItem($key);
    }

    /**
     * {@inheritdoc}
     */
    public function clear(string $prefix = ''): bool
    {
        return $this->pageCache->clear($prefix);
    }

    /**
     * {@inheritdoc}
     */
    public function clean($option = [])
    {
        return $this->clear();
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItem($key): bool
    {
        $this->pageCache->deleteItem($key);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteItems(array $keys): bool
    {
        $this->pageCache->deleteItems($keys);
    }

    /**
     * {@inheritdoc}
     */
    public function save(CacheItemInterface $item): bool
    {
        return $this->pageCache->save($item);
    }

    /**
     * {@inheritdoc}
     */
    public function saveDeferred(CacheItemInterface $item): bool
    {
        return $this->pageCache->saveDeferred($item);
    }

    /**
     * {@inheritdoc}
     */
    public function commit(): bool
    {
        return $this->pageCache->commit();
    }


    /**
     * {@inheritdoc}
     */
    public function prune(): bool
    {
        return $this->pageCache->prune();
    }


    /**
     * {@inheritdoc}
     */
    public function reset(): void
    {
        $this->pageCache->reset();
    }

    /**
     * Is Cache active.
     */
    public function isActive(): bool
    {
        return $this->activated;
    }

    public function getCacheTime(): string|int
    {
        return $this->expiresAfter;
    }
}
