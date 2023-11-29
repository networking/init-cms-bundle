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
    protected function configureListFields(ListMapper $list)
    {
        $this->getSonataAnnotationReader()->configureListFields($this->getClass(), $list);
    }

    /**
     * @param FormMapper $form
     */
    protected function configureFormFields(FormMapper $form)
    {
        $this->getSonataAnnotationReader()->configureFormFields($this->getClass(), $form);
    }

    /**
     * @param ShowMapper $show
     */
    protected function configureShowFields(ShowMapper $show)
    {
        $this->getSonataAnnotationReader()->configureShowFields($this->getClass(), $show);
    }

    /**
     * @param DatagridMapper $filter
     */
    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $this->getSonataAnnotationReader()->configureDatagridFilters($this->getClass(), $filter);
    }

    /**
     * @return SonataAdminAnnotationReaderInterface
     */
    protected function getSonataAnnotationReader()
    {
        return $this->getConfigurationPool()->getContainer()->get('networking_init_cms.annotation.reader');
    }
}
