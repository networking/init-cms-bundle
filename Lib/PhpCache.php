<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Lib;
umask(0002);
use phpFastCache;
use Symfony\Component\HttpFoundation\Request;

class PhpCache implements PhpCacheInterface {

    /**
     * @var phpFastCache
     */
    protected $phpFastCache;

    /**
     * @var string
     */
    protected $env;

    /**
     * @var string
     */
    protected $cacheDir;


    /**
     * @var boolean
     */

    protected $active;
    /**
     * @var string
     */
    protected $cache_time;

    /**
     * @param $type
     * @param $rootDir
     * @param $env
     * @throws \Exception
     */
    public function __construct($type = '', $rootDir = '', $env = '', $active = false, $cache_time = ''){
        $this->env = $env;
        $this->cacheDir = $this->createDir($rootDir, $env);
        $this->active = $active;
        $this->cache_time = $cache_time;

        phpFastCache::$default_chmod = 0755;
        phpFastCache::setup("storage", $type);
        phpFastCache::setup("path", $this->cacheDir );
        $this->phpFastCache = new phpFastCache();
    }

    protected function createDir($rootDir, $env){
        $cacheDir = $rootDir.sprintf('cache/%s/php_fast_cache', $env);
        if(!file_exists($cacheDir) || !is_writable($cacheDir)) {
            if(!file_exists($cacheDir)) {
                @mkdir($cacheDir,0777);
            }
            if(!is_writable($cacheDir)) {
                @chmod($cacheDir,0777);
            }
            if(!file_exists($cacheDir) || !is_writable($cacheDir)) {
                throw new \Exception("Sorry, Please create ".$cacheDir."/ and SET Mode 0777 or any Writable Permission!" , 100);
            }
        }

        return $cacheDir;
    }

    /**
     * @param Request $request
     * @return bool
     */
    public function isCacheable(Request $request){


        if($this->active == false)
        {   return false;
        }

        if($this->env != 'prod'){
            return false;
        }

        if($request->getUser()){
            return false;
        }

        if($request->getMethod() != 'GET'){
            return false;
        }

        return true;
    }

    /**
     * @param $keyword
     * @param array $option
     * @return mixed|null|string
     */
    public function get($keyword, $option = array()){
        return $this->phpFastCache->get($keyword, $option);
    }


    /**
     * @param $keyword
     * @param string $value
     * @param int $time
     * @param array $option
     * @return array|bool|string
     */
    public function set($keyword, $value = "", $time = 300, $option = array() ){
        return $this->phpFastCache->set($keyword, $value, $time, $option);
    }

    /**
     * @param $keyword
     * @param array $options
     * @return bool|\string[]
     */
    public function delete($keyword, $options = array()){
        return $this->phpFastCache->delete($keyword, $options);
    }

    /**
     * @param array $option
     * @return mixed
     */
    public function clean($option = array())
    {
        return $this->phpFastCache->clean($option);
    }

    /**
     * @param $keyword
     * @param int $time
     * @param array $option
     * @return bool
     */
    public function touch($keyword, $time = 300, $option = array()){
        return $this->phpFastCache->touch($keyword, $time, $option);
    }

    /**
     * @param $keyword
     * @param array $option
     * @return null
     */
    public function getInfo($keyword, $option = array()){
        return $this->phpFastCache->getInfo($keyword, $option);
    }

    /**
     * @return phpFastCache
     */
    public function getPhpFastCache()
    {
        return $this->phpFastCache;
    }

    /**
     * @param phpFastCache $phpFastCache
     */
    public function setPhpFastCache($phpFastCache)
    {
        $this->phpFastCache = $phpFastCache;
    }

}