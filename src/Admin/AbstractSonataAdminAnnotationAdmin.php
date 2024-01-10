<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Admin;

use Networking\InitCmsBundle\Reader\SonataAdminAnnotationReaderInterface;

use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;


abstract class AbstractSonataAdminAnnotationAdmin extends AbstractAdmin
{
    /**
     * @param ListMapper $list
     */
    protected function configureListFields(ListMapper $list): void
    {
        $this->getAnnotationReader()->configureListFields($this->getClass(), $list);
    }

    /**
     * @param FormMapper $form
     */
    protected function configureFormFields(FormMapper $form): void
    {
        $this->getAnnotationReader()->configureFormFields($this->getClass(), $form);
    }

    /**
     * @param ShowMapper $show
     */
    protected function configureShowFields(ShowMapper $show): void
    {
        $this->getAnnotationReader()->configureShowFields($this->getClass(), $show);
    }

    /**
     * @param DatagridMapper $filter
     */
    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        $this->getAnnotationReader()->configureDatagridFilters($this->getClass(), $filter);
    }

    /**
     * @return SonataAdminAnnotationReaderInterface
     */
    protected function getAnnotationReader()
    {
        return $this->getConfigurationPool()->getContainer()->get('networking_init_cms.annotation.reader');
    }
}
