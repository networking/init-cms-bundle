<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Admin;

use Networking\InitCmsBundle\AttributeReader\SonataAdminAttributeReaderInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;

trait SonataAdminAnnotationDatagridMapperTrait
{
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $this->getSonataAnnotationReader()->configureDatagridFilters($this->getClass(), $datagridMapper);
    }

    /**
     * @return SonataAdminAttributeReaderInterface
     */
    abstract protected function getSonataAnnotationReader();
}
