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


use Doctrine\ORM\Event\LifecycleEventArgs;
use Networking\InitCmsBundle\Lib\PhpCache;
use Networking\InitCmsBundle\Model\MenuItem;

class CacheCleaner {

    /**
     * @var int
     */
    protected $cleanCount = 0;
    /**
     * @var PhpCache
     */
    protected $phpCache;

    public function setPhpCache(PhpCache $phpCache){
        $this->phpCache = $phpCache;
    }

    public function postPersist(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        // perhaps you only want to act on some "Product" entity
        if ($entity instanceof MenuItem) {
            $this->cleanCache();
            // ... do something with the Product
        }
    }

    public function postUpdate(LifecycleEventArgs $args)
    {
        $entity = $args->getEntity();

        // perhaps you only want to act on some "Product" entity
        if ($entity instanceof MenuItem){
            $this->cleanCache();
        }
    }

    protected function cleanCache(){
        if($this->cleanCount < 1) {
            $this->cleanCount++;
            if(is_object($this->phpCache)){
                $this->phpCache->clean();
            }

        }
    }
} 