<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\AttributeReader;

use Networking\InitCmsBundle\Annotation\DatagridCallbackInterface;
use Networking\InitCmsBundle\Annotation\DatagridInterface;
use Networking\InitCmsBundle\Annotation\FormCallbackInterface;
use Networking\InitCmsBundle\Annotation\FormInterface;
use Networking\InitCmsBundle\Annotation\ListCallbackInterface;
use Networking\InitCmsBundle\Annotation\ListInterface;
use Networking\InitCmsBundle\Annotation\Order\FormReorderInterface;
use Networking\InitCmsBundle\Annotation\Order\ListReorderInterface;
use Networking\InitCmsBundle\Annotation\Order\ShowReorderInterface;
use Networking\InitCmsBundle\Annotation\ShowCallbackInterface;
use Networking\InitCmsBundle\Annotation\ShowInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Show\ShowMapper;

interface SonataAdminAttributeReaderInterface extends AttributeReaderInterface
{
    public const ANNOTATION_TYPE_ADMIN_LIST = 'ListInterface';
    public const ANNOTATION_TYPE_ADMIN_LIST_CALLBACK = 'ListCallbackInterface';
    public const ANNOTATION_TYPE_ADMIN_LIST_REORDER = 'ListReorderInterface';
    public const ANNOTATION_TYPE_ADMIN_LIST_EXCLUDE = 'ListExcludeInterface';
    public const ANNOTATION_TYPE_ADMIN_LIST_ALL = 'ListAllInterface';
    public const ANNOTATION_TYPE_ADMIN_SHOW = 'ShowInterface';
    public const ANNOTATION_TYPE_ADMIN_SHOW_REORDER = 'ShowReorderInterface';
    public const ANNOTATION_TYPE_ADMIN_SHOW_CALLBACK = 'ShowCallbackInterface';
    public const ANNOTATION_TYPE_ADMIN_SHOW_EXCLUDE = 'ShowExcludeInterface';
    public const ANNOTATION_TYPE_ADMIN_SHOW_ALL = 'ShowAllInterface';
    public const ANNOTATION_TYPE_ADMIN_DATAGRID = 'DatagridInterface';
    public const ANNOTATION_TYPE_ADMIN_DATAGRID_CALLBACK = 'DatagridCallbackInterface';
    public const ANNOTATION_TYPE_ADMIN_DATAGRID_EXCLUDE = 'DatagridExcludeInterface';
    public const ANNOTATION_TYPE_ADMIN_DATAGRID_ALL = 'DatagridAllInterface';
    public const ANNOTATION_TYPE_ADMIN_FORM = 'FormInterface';
    public const ANNOTATION_TYPE_ADMIN_FORM_REORDER = 'FormReorderInterface';
    public const ANNOTATION_TYPE_ADMIN_FORM_CALLBACK = 'FormCallbackInterface';
    public const ANNOTATION_TYPE_ADMIN_FORM_EXCLUDE = 'FormExcludeInterface';
    public const ANNOTATION_TYPE_ADMIN_FORM_ALL = 'FormAllInterface'
    ;

    /**
     * @return ListInterface[]
     */
    public function getListMapperAnnotations($entity): array;

    /**
     * @return ListCallbackInterface[]
     */
    public function getListMapperCallbacks($entity): array;

    /**
     * @return ListReorderInterface|null
     */
    public function getListReorderAnnotation($entity): ?ListReorderInterface;

    /**
     * @return ShowInterface[]
     */
    public function getShowMapperAnnotations($entity): array;

    /**
     * @return ShowCallbackInterface[]
     */
    public function getShowMapperCallbacks($entity): array;

    /**
     * @return ShowReorderInterface[]
     */
    public function getShowReorderAnnotations($entity): array;

    /**
     * @return FormInterface[]
     */
    public function getFormMapperAnnotations($entity): array;

    /**
     * @return FormCallbackInterface[]
     */
    public function getFormMapperCallbacks($entity): array;

    /**
     * @return FormReorderInterface[]
     */
    public function getFormReorderAnnotations($entity): array;

    /**
     * @return DatagridInterface[]
     */
    public function getDatagridMapperAnnotation($entity): array;

    /**
     * @return DatagridCallbackInterface[]
     */
    public function getDatagridMapperCallbacks($entity): array;

    /**
     * @return void
     */
    public function configureListFields($entity, ListMapper $listMapper): void;

    /**
     * @return void
     */
    public function configureFormFields($entity, FormMapper $formMapper): void;

    /**
     * @return void
     */
    public function configureShowFields($entity, ShowMapper $showMapper): void;

    /**
     * @return void
     */
    public function configureDatagridFilters($entity, DatagridMapper $datagridMapper): void;
}
