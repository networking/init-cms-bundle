<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Admin;

use Networking\InitCmsBundle\AttributeReader\SonataAdminAttributeReaderInterface;
use Sonata\AdminBundle\Admin\Pool;

trait SonataAdminAnnotationReaderTrait
{
    /**
     * @return SonataAdminAttributeReaderInterface
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
