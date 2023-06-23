<?php

declare(strict_types=1);

namespace Networking\InitCmsBundle\AnnotationReader;

use Doctrine\Common\Annotations\Reader;

interface AnnotationReaderInterface
{
    public const
        SCOPE_CLASS = 'class',
        SCOPE_METHOD = 'method',
        SCOPE_PROPERTY = 'property'
    ;

    /**
     * AnnotationReaderInterface constructor.
     * @param Reader $annotationReader
     */
    public function __construct(Reader $annotationReader);

    /**
     * @param mixed $entity
     * @return array
     */
    public function getAnnotations($entity);

    /**
     * @param mixed $entity
     * @param string $type
     * @param string $scope
     * @return array
     */
    public function getAnnotationsByType($entity, $type, $scope);
}
