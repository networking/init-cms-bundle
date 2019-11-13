<?php
/**
 * Created by PhpStorm.
 * User: reichmuth
 * Date: 11.02.15
 * Time: 15:46.
 */

namespace Networking\InitCmsBundle\Lib;

use Networking\InitCmsBundle\Cache\PageCacheInterface;
use Symfony\Component\HttpFoundation\Request;

@trigger_error(sprintf('The "%s" interface is deprecated since InitCms 4.1, use "%s"  instead.', PhpCacheInterface::class,  PhpCacheInterface::class), E_USER_DEPRECATED);

/**
 * Interface PhpCacheInterface
 * @deprecated
 * @package Networking\InitCmsBundle\Lib
 */
interface PhpCacheInterface extends PageCacheInterface
{

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


}
