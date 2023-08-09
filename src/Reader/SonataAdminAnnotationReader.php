<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\Reader;

use Networking\InitCmsBundle\AnnotationReader\AnnotationReader;
use Networking\InitCmsBundle\Annotation\DatagridCallbackInterface;
use Networking\InitCmsBundle\Annotation\DatagridMapper;
use Networking\InitCmsBundle\Annotation\FormCallbackInterface;
use Networking\InitCmsBundle\Annotation\FormMapper;
use Networking\InitCmsBundle\Annotation\ListCallbackInterface;
use Networking\InitCmsBundle\Annotation\ListMapper;
use Networking\InitCmsBundle\Annotation\Order\FormReorderInterface;
use Networking\InitCmsBundle\Annotation\Order\ListReorderInterface;
use Networking\InitCmsBundle\Annotation\Order\ShowReorderInterface;
use Networking\InitCmsBundle\Annotation\ShowCallbackInterface;
use Networking\InitCmsBundle\Annotation\ShowMapper;
use Sonata\AdminBundle\Admin\AdminInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper as SonataDatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper as SonataListMapper;
use Sonata\AdminBundle\Form\FormMapper as SonataFormMapper;
use Sonata\AdminBundle\Show\ShowMapper as SonataShowMapper;

class SonataAdminAnnotationReader extends AnnotationReader implements SonataAdminAnnotationReaderInterface
{
    /**
     * @inheritdoc
     */
    public function getListMapperAnnotations($entity)
    {
        $listAnnotations = $this->getAnnotationsByType($entity, self::ANNOTATION_TYPE_ADMIN_LIST, self::SCOPE_PROPERTY);
        if (!$this->getAnnotationsByType($entity, self::ANNOTATION_TYPE_ADMIN_LIST_ALL, self::SCOPE_CLASS)) {
            return $listAnnotations;
        }

        $listExcludeAnnotations = $this->getAnnotationsByType($entity, self::ANNOTATION_TYPE_ADMIN_LIST_EXCLUDE, self::SCOPE_PROPERTY);

        return $this->collectAllAnnotations(
            $listAnnotations,
            $listExcludeAnnotations,
            $this->getReflectionProperties($entity),
            new ListMapper()
        );
    }

    /**
     * @inheritdoc
     */
    public function getShowMapperAnnotations($entity)
    {
        $showAnnotations = $this->getAnnotationsByType($entity, self::ANNOTATION_TYPE_ADMIN_SHOW, self::SCOPE_PROPERTY);
        if (!$this->getAnnotationsByType($entity, self::ANNOTATION_TYPE_ADMIN_SHOW_ALL, self::SCOPE_CLASS)) {
            return $showAnnotations;
        }

        $showExcludeAnnotations = $this->getAnnotationsByType($entity, self::ANNOTATION_TYPE_ADMIN_SHOW_EXCLUDE, self::SCOPE_PROPERTY);

        return $this->collectAllAnnotations(
            $showAnnotations,
            $showExcludeAnnotations,
            $this->getReflectionProperties($entity),
            new ShowMapper()
        );
    }

    /**
     * @inheritdoc
     */
    public function getFormMapperAnnotations($entity)
    {
        $formAnnotations = $this->getAnnotationsByType($entity, self::ANNOTATION_TYPE_ADMIN_FORM, self::SCOPE_PROPERTY);

        if (!$this->getAnnotationsByType($entity, self::ANNOTATION_TYPE_ADMIN_FORM_ALL, self::SCOPE_CLASS)) {
            return $formAnnotations;
        }

        $formExcludeAnnotations = $this->getAnnotationsByType($entity, self::ANNOTATION_TYPE_ADMIN_FORM_EXCLUDE, self::SCOPE_PROPERTY);

        return $this->collectAllAnnotations(
            $formAnnotations,
            $formExcludeAnnotations,
            $this->getReflectionProperties($entity),
            new FormMapper()
        );
    }

    /**
     * @inheritdoc
     */
    public function getDatagridMapperAnnotation($entity)
    {
        $datagridAnnotations = $this->getAnnotationsByType($entity, self::ANNOTATION_TYPE_ADMIN_DATAGRID, self::SCOPE_PROPERTY);
        if (!$this->getAnnotationsByType($entity, self::ANNOTATION_TYPE_ADMIN_DATAGRID_ALL, self::SCOPE_CLASS)) {
            return $datagridAnnotations;
        }

        $datagridExcludeAnnotations = $this->getAnnotationsByType($entity, self::ANNOTATION_TYPE_ADMIN_DATAGRID_EXCLUDE, self::SCOPE_PROPERTY);

        return $this->collectAllAnnotations(
            $datagridAnnotations,
            $datagridExcludeAnnotations,
            $this->getReflectionProperties($entity),
            new DatagridMapper()
        );
    }

    /**
     * @inheritdoc
     */
    public function configureListFields($entity, SonataListMapper $listMapper)
    {
        $annotations = $this->getListMapperAnnotations($entity);

        foreach ($annotations as $propertyName => $annotation) {
            $method = $annotation->isIdentifier() ? 'addIdentifier' : 'add';

            $fieldDescriptionOptions = $annotation->getFieldDescriptionOptions();
            $routeName = $annotation->getRouteName();
            if ($routeName) {
                $fieldDescriptionOptions['route'] = ['name' => $routeName];
            }

            $listMapper->$method(
                $annotation->getName() ?: $propertyName,
                $annotation->getType(),
                $fieldDescriptionOptions
            );
        }

        $this->invokeCallbacks($entity, $this->getListMapperCallbacks($entity), [$listMapper]);

        $reorder = $this->getListReorderAnnotation($entity);
        if ($reorder) {
            $listMapper->reorder($reorder->getKeys());
        }
    }

    /**
     * @inheritdoc
     */
    public function configureFormFields($entity, SonataFormMapper $formMapper)
    {
        $annotations = $this->getFormMapperAnnotations($entity);

        foreach ($annotations as $propertyName => $annotation) {
            $admin = $formMapper->getAdmin();

            /** @var AdminInterface $rootAdmin */
            if (method_exists($admin, 'getRoot') && $rootAdmin = $admin->getRoot()) {
                if ($rootAdmin->getCode() !== $admin->getCode() && $annotation->isIgnoreOnParent()) {
                    continue;
                }
            }

            $hasTab = false;
            if ($tab = $annotation->getTab()) {
                $hasTab = true;
                $formMapper->tab($tab, $annotation->getTabOptions());
            }

            $with = $annotation->getWith() ?: $formMapper->getAdmin()->getLabel();
            $formMapper->with($with, $annotation->getWithOptions());

            $formMapper->add(
                $annotation->getName() ?: $propertyName,
                $annotation->getType(),
                $annotation->getOptions(),
                $annotation->getFieldDescriptionOptions()
            );

            $formMapper->end();

            if ($hasTab) {
                $formMapper->end();
            }
        }

        $this->invokeCallbacks($entity, $this->getFormMapperCallbacks($entity), [$formMapper]);

        foreach ($this->getFormReorderAnnotations($entity) as $formReorderAnnotation) {
            $reorderWith = $formReorderAnnotation->getWith() ?: $formMapper->getAdmin()->getLabel();
            $formMapper->with($reorderWith);
            $formMapper->reorder($formReorderAnnotation->getKeys());
            $formMapper->end();
        }
    }

    /**
     * @inheritdoc
     */
    public function configureShowFields($entity, SonataShowMapper $showMapper)
    {
        $annotations = $this->getShowMapperAnnotations($entity);

        foreach ($annotations as $propertyName => $annotation) {
            $hasTab = false;
            if ($tab = $annotation->getTab()) {
                $hasTab = true;
                $showMapper->tab($tab, $annotation->getTabOptions());
            }

            $with = $annotation->getWith() ?: $showMapper->getAdmin()->getLabel();
            $showMapper->with($with, $annotation->getWithOptions());

            $showMapper->add(
                $annotation->getName() ?: $propertyName,
                $annotation->getType(),
                $annotation->getFieldDescriptionOptions()
            );

            $showMapper->end();

            if ($hasTab) {
                $showMapper->end();
            }
        }

        $this->invokeCallbacks($entity, $this->getShowMapperCallbacks($entity), [$showMapper]);

        foreach ($this->getShowReorderAnnotations($entity) as $showReorderAnnotation) {
            $reorderWith = $showReorderAnnotation->getWith() ?: $showMapper->getAdmin()->getLabel();
            $showMapper->with($reorderWith);
            $showMapper->reorder($showReorderAnnotation->getKeys());
            $showMapper->end();
        }
    }

    /**
     * @inheritdoc
     */
    public function configureDatagridFilters($entity, SonataDatagridMapper $datagridMapper)
    {
        $annotations = $this->getDatagridMapperAnnotation($entity);

        foreach ($annotations as $propertyName => $annotation) {
            $datagridMapper->add(
                $annotation->getName() ?: $propertyName,
                $annotation->getType(),
                $annotation->getFilterOptions(),
                $annotation->getFieldType()
            );
        }

        $this->invokeCallbacks($entity, $this->getDatagridMapperCallbacks($entity), [$datagridMapper]);
    }

    /**
     * @param $entity
     * @return FormCallbackInterface[]
     */
    public function getFormMapperCallbacks($entity)
    {
        
        return $this->getAnnotationsByType($entity, self::ANNOTATION_TYPE_ADMIN_FORM_CALLBACK, self::SCOPE_METHOD);
    }

    /**
     * @param $entity
     * @return ShowCallbackInterface[]
     */
    public function getShowMapperCallbacks($entity)
    {
        return $this->getAnnotationsByType($entity, self::ANNOTATION_TYPE_ADMIN_SHOW_CALLBACK, self::SCOPE_METHOD);
    }

    /**
     * @param mixed $entity
     * @return ListCallbackInterface[]
     */
    public function getListMapperCallbacks($entity)
    {
        return $this->getAnnotationsByType($entity, self::ANNOTATION_TYPE_ADMIN_LIST_CALLBACK, self::SCOPE_METHOD);
    }

    /**
     * @param $entity
     * @return DatagridCallbackInterface[]
     */
    public function getDatagridMapperCallbacks($entity)
    {
        return $this->getAnnotationsByType($entity, self::ANNOTATION_TYPE_ADMIN_DATAGRID_CALLBACK, self::SCOPE_METHOD);
    }

    /**
     * @param $entity
     * @return ListReorderInterface
     */
    public function getListReorderAnnotation($entity)
    {
        return $this->getAnnotationsByType($entity, self::ANNOTATION_TYPE_ADMIN_LIST_REORDER, self::SCOPE_CLASS);
    }

    /**
     * @param $entity
     * @return FormReorderInterface[]
     */
    public function getFormReorderAnnotations($entity)
    {
        $annotations = $this->getAnnotations($entity);
        $scopeAnnotations = $annotations[self::SCOPE_CLASS];
        if (!isset($scopeAnnotations[self::ANNOTATION_TYPE_ADMIN_FORM_REORDER])) {
            return [];
        }

        return $scopeAnnotations[self::ANNOTATION_TYPE_ADMIN_FORM_REORDER];
    }

    /**
     * @param $entity
     * @return ShowReorderInterface[]
     */
    public function getShowReorderAnnotations($entity)
    {
        $annotations = $this->getAnnotations($entity);
        $scopeAnnotations = $annotations[self::SCOPE_CLASS];
        if (!isset($scopeAnnotations[self::ANNOTATION_TYPE_ADMIN_SHOW_REORDER])) {
            return [];
        }

        return $scopeAnnotations[self::ANNOTATION_TYPE_ADMIN_SHOW_REORDER];
    }

    /**
     * @return array
     */
    protected function collectAllAnnotations(array $foundAnnotations, array $excludedAnnotations, array $properties, mixed $prototypeAnnotation)
    {
        $annotations = [];

        foreach ($properties as $reflectionProperty) {
            $propertyName = $reflectionProperty->getName();
            if (isset($excludedAnnotations[$propertyName])) {
                continue;
            }
            if (isset($foundAnnotations[$propertyName])) {
                $annotations[$propertyName] = $foundAnnotations[$propertyName];
                continue;
            }
            $annotations[$propertyName] = clone $prototypeAnnotation;
        }

        return $annotations;
    }

    /**
     * @param string $className
     * @return \ReflectionProperty[]
     */
    protected function getReflectionProperties($className): array
    {
        $reflectionClass = new \ReflectionClass($className);
        return $reflectionClass->getProperties();
    }

    /**
     * @param string $entity
     */
    protected function invokeCallbacks($entity, array $callbacks, array $args)
    {
        if (count($callbacks) > 0) {
            $classReflection = new \ReflectionClass($entity);
            foreach ($callbacks as $methodName => $annotation) {
                $method = $classReflection->getMethod($methodName);
                $method->invokeArgs(null, $args);
            }
        }
    }
}
