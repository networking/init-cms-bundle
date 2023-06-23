<?php

declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: reichmuth
 * Date: 11.02.15
 * Time: 15:46.
 */
namespace Networking\InitCmsBundle\Cache;

use Symfony\Component\HttpFoundation\Request;

interface PageCacheInterface
{
    /**
     * @param Request $request
     * @param $user
     *
     * @return bool
     */
    public function isCacheable(Request $request, $user);

    /**
     * @param array $option
     *
     * @return mixed
     */
    public function clean($option = []);

    /**
     * @param $keyword
     * @return mixed|string|null
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function get($keyword);

    /**
     * @param $keyword
     * @param string $value
     * @param null $time
     * @return array|bool|string
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function set($keyword, $value = '', $time = 300);

    /**
     * @param $keyword
     * @return bool|string[]
     * @throws \Psr\Cache\InvalidArgumentException
     */
    public function delete($keyword);


    /**
     * Is Cache active.
     *
     * @return bool
     */
    public function isActive();

    /**
     * @return string
     */
    public function getCacheTime();
}
