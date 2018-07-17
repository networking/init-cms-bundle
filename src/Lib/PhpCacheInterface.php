<?php
/**
 * Created by PhpStorm.
 * User: reichmuth
 * Date: 11.02.15
 * Time: 15:46.
 */

namespace Networking\InitCmsBundle\Lib;

use Symfony\Component\HttpFoundation\Request;

interface PhpCacheInterface
{
    /**
     * @param Request $request
     * @param $user
     *
     * @return bool
     */
    public function isCacheable(Request $request, $user);

    /**
     * @param $keyword
     * @param array $option
     *
     * @return mixed|null|string
     */
    public function get($keyword, $option = []);

    /**
     * @param $keyword
     * @param string $value
     * @param int    $time
     * @param array  $option
     *
     * @return array|bool|string
     */
    public function set($keyword, $value = '', $time = 300, $option = []);

    /**
     * @param $keyword
     * @param array $options
     *
     * @return bool|\string[]
     */
    public function delete($keyword, $options = []);

    /**
     * @param array $option
     *
     * @return mixed
     */
    public function clean($option = []);

    /**
     * @param $keyword
     * @param int   $time
     * @param array $option
     *
     * @return bool
     */
    public function touch($keyword, $time = 300, $option = []);

    /**
     * @param $keyword
     * @param array $option
     */
    public function getInfo($keyword, $option = []);

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
