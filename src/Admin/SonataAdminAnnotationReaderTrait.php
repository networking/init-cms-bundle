<?php

namespace Networking\InitCmsBundle\Admin;

use Networking\InitCmsBundle\Reader\SonataAdminAnnotationReaderInterface;
use Sonata\AdminBundle\Admin\Pool;

trait SonataAdminAnnotationReaderTrait
{
    /**
     * @return SonataAdminAnnotationReaderInterface
     */
    protected function getSonataAnnotationReader()
    {
        return $this->getConfigurationPool()->getContainer()->get('networking_init_cms.annotation.reader');
    }

    /**
     * @return Pool
     */
    abstract protected function getConfigurationPool();
}