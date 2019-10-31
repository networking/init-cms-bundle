<?php

namespace Networking\InitCmsBundle\Admin;

use Networking\InitCmsBundle\Reader\SonataAdminAnnotationReaderInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;

trait SonataAdminAnnotationListMapperTrait
{
    use SonataAdminAnnotationReaderTrait;

    /**
     * @param ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {
        $this->getSonataAnnotationReader()->configureListFields($this->getClass(), $listMapper);
    }

    /**
     * @return SonataAdminAnnotationReaderInterface
     */
    abstract protected function getSonataAnnotationReader();
}