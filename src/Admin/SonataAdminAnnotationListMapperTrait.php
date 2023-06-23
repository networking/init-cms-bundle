<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Admin;

use Networking\InitCmsBundle\Reader\SonataAdminAnnotationReaderInterface;
use Sonata\AdminBundle\Datagrid\ListMapper;

trait SonataAdminAnnotationListMapperTrait
{
    use SonataAdminAnnotationReaderTrait;

    protected function configureListFields(ListMapper $listMapper)
    {
        $this->getSonataAnnotationReader()->configureListFields($this->getClass(), $listMapper);
    }

    /**
     * @return SonataAdminAnnotationReaderInterface
     */
    abstract protected function getSonataAnnotationReader();
}
