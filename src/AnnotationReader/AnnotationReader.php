<?php

namespace Networking\InitCmsBundle\AnnotationReader;

use Doctrine\Common\Annotations\Reader;

class AnnotationReader implements AnnotationReaderInterface
{
    /**
     * @var Reader
     */
    protected $annotationReader;

    /**
     * @var array
     */
    protected static $cache = [];

    /**
     * AnnotationReader constructor.
     * @param Reader $annotationReader
     */
    public function __construct(Reader $annotationReader)
    {
        $this->annotationReader = $annotationReader;
    }

    /**
     * @inheritdoc
     */
    public function getAnnotations($entity)
    {
        $reflectionClass = new \ReflectionClass($entity);
        $className = $reflectionClass->getName();

        if(isset(self::$cache[$className])){
            return self::$cache[$className];
        }
        $annotations = array(
            self::SCOPE_CLASS => $this->getClassScopeAnnotations($reflectionClass),
            self::SCOPE_METHOD => $this->getMethodScopeAnnotations($reflectionClass),
            self::SCOPE_PROPERTY => $this->getPropertyScopeAnnotations($reflectionClass),
        );



        return self::$cache[$className] = $annotations;
    }

    /**
     * @inheritdoc
     */
    public function getAnnotationsByType($entity, $type, $scope)
    {
        $returnAnnotations = [];
        $annotations = $this->getAnnotations($entity);
        $scopeAnnotations = isset($annotations[$scope]) ? $annotations[$scope] : [];

        if($scope === self::SCOPE_CLASS){
            return isset($scopeAnnotations[$type]) ? reset($scopeAnnotations[$type]) : null;
        }

        foreach($scopeAnnotations as $fieldName => $annotationTypes){
            if(isset($annotationTypes[$type])){
                $returnAnnotations[$fieldName] = reset($annotationTypes[$type]);
            }
        }

        return $returnAnnotations;
    }

    /**
     * @param \ReflectionClass $reflectionClass
     * @return array
     */
    protected function getPropertyScopeAnnotations(\ReflectionClass $reflectionClass){
        $annotations = [];

        foreach($reflectionClass->getProperties() as $reflectionProperty){
            $fieldName = $reflectionProperty->getName();
            if(!isset($annotations[$fieldName])){
                $annotations[$fieldName] = [];
            }

            foreach($this->annotationReader->getPropertyAnnotations($reflectionProperty) as $propertyAnnotation){

                $reflectionAnnotation = new \ReflectionClass($propertyAnnotation);

                $explode = explode("\\", $reflectionAnnotation->getName());
                $type = end($explode);

                foreach($reflectionAnnotation->getInterfaces() as $reflectionInterface){
                    $explode = explode("\\", $reflectionInterface->getName());
                    $type = end($explode);

                    if(!isset($annotations[$fieldName][$type])){
                        $annotations[$fieldName][$type] = [];
                    }

                    $annotations[$fieldName][$type][] = $propertyAnnotation;
                }


                if(!isset($annotations[$fieldName][$type])){
                    $annotations[$fieldName][$type] = [];
                }

                $annotations[$fieldName][$type][] = $propertyAnnotation;
            }

            foreach($reflectionProperty->getAttributes() as $reflectionAttribute){

                $explode = explode("\\", $reflectionAttribute->getName());
                $type = end($explode);

                $reflectionAnnotation = new \ReflectionClass($reflectionAttribute->getName());
                foreach($reflectionAnnotation->getInterfaces() as $reflectionInterface){
                    $explode = explode("\\", $reflectionInterface->getName());
                    $type = end($explode);
                    
                }
                if(!isset($annotations[$fieldName][$type])){
                    $annotations[$fieldName][$type] = [];
                }

                $annotations[$fieldName][$type][] = $reflectionAttribute->newInstance();
            }
        }

        $parentClass = $reflectionClass->getParentClass();
        if($parentClass){
            $annotations = array_merge_recursive($annotations, $this->getPropertyScopeAnnotations($parentClass));
        }

        return $annotations;
    }

    /**
     * @param \ReflectionClass $reflectionClass
     * @return array
     */
    protected function getMethodScopeAnnotations(\ReflectionClass $reflectionClass){
        $annotations = [];

        foreach($reflectionClass->getMethods() as $reflectionMethod){
            $methodName = $reflectionMethod->getName();
            if(!isset($annotations[$methodName])){
                $annotations[$methodName] = [];
            }
            foreach($this->annotationReader->getMethodAnnotations($reflectionMethod) as $methodAnnotation){


                $explode = explode("\\", $methodAnnotation->getName());
                $type = end($explode);
                $reflectionAnnotation = new \ReflectionClass($methodAnnotation);

                foreach($reflectionAnnotation->getInterfaces() as $reflectionInterface){
                    $explode = explode("\\", $reflectionInterface->getName());
                    $type = end($explode);
                }

                if(!isset($annotations[$methodName][$type])){
                    $annotations[$methodName][$type] = [];
                }

                $annotations[$methodName][$type][] = $methodAnnotation;
            }

            foreach($reflectionMethod->getAttributes() as $reflectionAttribute){
                $explode = explode("\\", $reflectionAttribute->getName());
                $type = end($explode);

                $reflectionAnnotation = new \ReflectionClass($reflectionAttribute->getName());
                foreach($reflectionAnnotation->getInterfaces() as $reflectionInterface)
                {
                    $explode = explode("\\", $reflectionInterface->getName());
                    $type = end($explode);
                }

                if(!isset($annotations[$methodName][$type])){
                    $annotations[$methodName][$type] = [];
                }

                $annotations[$methodName][$type][] = $reflectionAttribute->newInstance();
            }
        }


        $parentClass = $reflectionClass->getParentClass();
        if($parentClass){
            $annotations = array_merge_recursive($annotations, $this->getMethodScopeAnnotations($parentClass));
        }

        return $annotations;
    }

    /**
     * @param \ReflectionClass $reflectionClass
     * @return array
     */
    protected function getClassScopeAnnotations(\ReflectionClass $reflectionClass)
    {
        $annotations = [];

        foreach($this->annotationReader->getClassAnnotations($reflectionClass) as $classAnnotation){
            $reflectionAnnotation = new \ReflectionClass($classAnnotation);
            $explode = explode("\\", $reflectionAnnotation->getName());
            $type = end($explode);

            foreach($reflectionAnnotation->getInterfaces() as $reflectionInterface){
                $explode = explode("\\", $reflectionInterface->getName());
                $type = end($explode);

            }

            if(!isset($annotations[$type])){
                $annotations[$type] = [];
            }

            $annotations[$type][] = $classAnnotation;
        }

        foreach($reflectionClass->getAttributes() as $reflectionAttribute){
            $reflectionAnnotation = new \ReflectionClass($reflectionAttribute->getName());

            $explode = explode("\\", $reflectionAnnotation->getName());
            $type = end($explode);

            foreach($reflectionAnnotation->getInterfaces() as $reflectionInterface){
                $explode = explode("\\", $reflectionInterface->getName());
                $type = end($explode);

            }

            if(!isset($annotations[$type])){
                $annotations[$type] = [];
            }

            $annotations[$type][] = $reflectionAttribute->newInstance();
        }

        $parentClass = $reflectionClass->getParentClass();
        if($parentClass){
            $annotations = array_merge_recursive($annotations, $this->getClassScopeAnnotations($parentClass));
        }

        return $annotations;
    }
}