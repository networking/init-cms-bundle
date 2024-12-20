<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Admin;

use Networking\InitCmsBundle\AttributeReader\SonataAdminAttributeReaderInterface;
use Sonata\AdminBundle\Form\FormMapper;

trait SonataAdminAnnotationFormMapperTrait
{
    use SonataAdminAnnotationReaderTrait;

    protected function configureFormFields(FormMapper $formMapper)
    {
        $this->getSonataAnnotationReader()->configureFormFields($this->getClass(), $formMapper);
    }

    /**
     * @return SonataAdminAttributeReaderInterface
     */
    abstract protected function getSonataAnnotationReader();
}
