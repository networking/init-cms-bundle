<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\AttributeReader;

class AttributeReader implements AttributeReaderInterface
{
    /**
     * @var array
     */
    protected static $cache = [];

    public function getAnnotations($entity)
    {
        $reflectionClass = new \ReflectionClass($entity);
        $className = $reflectionClass->getName();

        if (isset(self::$cache[$className])) {
            return self::$cache[$className];
        }
        $annotations = [
            self::SCOPE_CLASS => $this->getClassScopeAnnotations(
                $reflectionClass
            ),
            self::SCOPE_METHOD => $this->getMethodScopeAnnotations(
                $reflectionClass
            ),
            self::SCOPE_PROPERTY => $this->getPropertyScopeAnnotations(
                $reflectionClass
            ),
        ];

        return self::$cache[$className] = $annotations;
    }

    public function getAnnotationsByType($entity, $type, $scope)
    {
        $returnAnnotations = [];
        $annotations = $this->getAnnotations($entity);

        $scopeAnnotations = $annotations[$scope] ?? [];

        if (self::SCOPE_CLASS === $scope) {
            return isset($scopeAnnotations[$type]) ? reset(
                $scopeAnnotations[$type]
            ) : null;
        }

        foreach ($scopeAnnotations as $fieldName => $annotationTypes) {
            if (isset($annotationTypes[$type])) {
                $returnAnnotations[$fieldName] = reset($annotationTypes[$type]);
            }
        }

        return $returnAnnotations;
    }

    /**
     * @return array
     */
    protected function getPropertyScopeAnnotations(
        \ReflectionClass $reflectionClass
    ) {
        $annotations = [];

        foreach ($reflectionClass->getProperties() as $reflectionProperty) {
            $fieldName = $reflectionProperty->getName();
            if (!isset($annotations[$fieldName])) {
                $annotations[$fieldName] = [];
            }

            foreach (
                $reflectionProperty->getAttributes() as $reflectionAttribute
            ) {
                $explode = explode('\\', $reflectionAttribute->getName());
                $type = end($explode);

                $reflectionAnnotation = new \ReflectionClass(
                    $reflectionAttribute->getName()
                );
                foreach (
                    $reflectionAnnotation->getInterfaces() as $reflectionInterface
                ) {
                    $explode = explode('\\', $reflectionInterface->getName());
                    $type = end($explode);
                }
                if (!isset($annotations[$fieldName][$type])) {
                    $annotations[$fieldName][$type] = [];
                }

                $annotations[$fieldName][$type][]
                    = $reflectionAttribute->newInstance();
            }
        }

        $parentClass = $reflectionClass->getParentClass();
        if ($parentClass) {
            $annotations = array_merge_recursive(
                $annotations,
                $this->getPropertyScopeAnnotations($parentClass)
            );
        }

        return $annotations;
    }

    /**
     * @return array
     */
    protected function getMethodScopeAnnotations(
        \ReflectionClass $reflectionClass
    ) {
        $annotations = [];

        foreach ($reflectionClass->getMethods() as $reflectionMethod) {
            $methodName = $reflectionMethod->getName();
            if (!isset($annotations[$methodName])) {
                $annotations[$methodName] = [];
            }

            foreach ($reflectionMethod->getAttributes() as $reflectionAttribute) {
                $explode = explode('\\', $reflectionAttribute->getName());
                $type = end($explode);

                $reflectionAnnotation = new \ReflectionClass(
                    $reflectionAttribute->getName()
                );
                foreach (
                    $reflectionAnnotation->getInterfaces() as $reflectionInterface
                ) {
                    $explode = explode('\\', $reflectionInterface->getName());
                    $type = end($explode);
                }

                if (!isset($annotations[$methodName][$type])) {
                    $annotations[$methodName][$type] = [];
                }

                $annotations[$methodName][$type][]
                    = $reflectionAttribute->newInstance();
            }
        }

        $parentClass = $reflectionClass->getParentClass();
        if ($parentClass) {
            $annotations = array_merge_recursive(
                $annotations,
                $this->getMethodScopeAnnotations($parentClass)
            );
        }

        return $annotations;
    }

    /**
     * @return array
     */
    protected function getClassScopeAnnotations(
        \ReflectionClass $reflectionClass
    ) {
        $annotations = [];

        foreach ($reflectionClass->getAttributes() as $reflectionAttribute) {
            $reflectionAnnotation = new \ReflectionClass(
                $reflectionAttribute->getName()
            );

            $explode = explode('\\', $reflectionAnnotation->getName());
            $type = end($explode);

            foreach (
                $reflectionAnnotation->getInterfaces() as $reflectionInterface
            ) {
                $explode = explode('\\', $reflectionInterface->getName());
                $type = end($explode);
            }

            if (!isset($annotations[$type])) {
                $annotations[$type] = [];
            }

            $annotations[$type][] = $reflectionAttribute->newInstance();
        }

        $parentClass = $reflectionClass->getParentClass();
        if ($parentClass) {
            $annotations = array_merge_recursive(
                $annotations,
                $this->getClassScopeAnnotations($parentClass)
            );
        }

        return $annotations;
    }
}
