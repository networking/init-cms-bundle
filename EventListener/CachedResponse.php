<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\EventListener;


use Networking\InitCmsBundle\Lib\PhpCache;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class CachedResponse {

    /**
     * @var PhpCache
     */
    protected $phpCache;

    public function __construct(PhpCache $phpCache){
        $this->phpCache = $phpCache;
    }

    public function onKernelRequest(GetResponseEvent $event){
//        $request = $event->getRequest();
//        if($this->phpCache->isCacheable($request)){
//            if($response = $this->phpCache->get($request->getLocale().$request->getUri())){
//                $response->send();
//                die;
//                $event->stopPropagation();
//            }
//        }
    }
} 