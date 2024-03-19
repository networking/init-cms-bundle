<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Admin;

use Networking\InitCmsBundle\AttributeReader\SonataAdminAttributeReaderInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

abstract class AbstractAttributeAdmin extends AbstractAdmin
{
    protected SonataAdminAttributeReaderInterface $attributeReader;

    public function __construct(
        SonataAdminAttributeReaderInterface $attributeReader
    ) {
        $this->attributeReader = $attributeReader;

        parent::__construct();
    }

    protected function configureListFields(ListMapper $list): void
    {
        $this->attributeReader->configureListFields(
            $this->getClass(),
            $list
        );
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $this->attributeReader->configureFormFields(
            $this->getClass(),
            $form
        );
    }

    protected function configureShowFields(ShowMapper $show): void
    {
        $this->attributeReader->configureShowFields(
            $this->getClass(),
            $show
        );
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $this->attributeReader->configureDatagridFilters(
            $this->getClass(),
            $filter
        );
    }
}
