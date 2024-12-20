<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Admin;

use Networking\InitCmsBundle\AttributeReader\SonataAdminAttributeReaderInterface;
use Sonata\AdminBundle\Show\ShowMapper;

trait SonataAdminAnnotationShowMapperTrait
{
    use SonataAdminAnnotationReaderTrait;

    protected function configureShowFields(ShowMapper $showMapper)
    {
        $this->getSonataAnnotationReader()->configureShowFields($this->getClass(), $showMapper);
    }

    /**
     * @return SonataAdminAttributeReaderInterface
     */
    abstract protected function getSonataAnnotationReader();
}
