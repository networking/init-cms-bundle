<?php

declare(strict_types=1);

/**
 * This file is part of the ubs package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Form\Type;

use Networking\InitCmsBundle\Reader\SonataAdminAnnotationReaderInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class ContentType extends AbstractType
{
    /**
     * @var \Networking\InitCmsBundle\Reader\SonataAdminAnnotationReaderInterface
     */
    protected $annotationReader;

    public function __construct(
        SonataAdminAnnotationReaderInterface $annotationReader
    ) {
        $this->annotationReader = $annotationReader;
    }

    public function buildForm(FormBuilderInterface $builder, array $options): void
    {
        $annotations = $this->annotationReader->getFormMapperAnnotations($options['class']);

        $defaultValue = null;

        foreach ($annotations as $propertyName => $annotation) {
            $fieldName = $annotation->getName() ?: $propertyName;
            $builder->add(
                $fieldName,
                $annotation->getType(),
                array_merge($annotation->getOptions(), [])
            );
        }
        $this->invokeCallbacks($options['class'], $this->annotationReader->getFormMapperCallbacks($options['class']), [$builder]);
        $options['data_class'] = $options['class'];
    }

    /**
     *
     *
     * @throws \RuntimeException
     */
    protected function getFieldDescription(array $options): \Sonata\AdminBundle\Admin\FieldDescriptionInterface
    {
        if (!isset($options['sonata_field_description'])) {
            throw new \RuntimeException('Please provide a valid `sonata_field_description` option');
        }

        return $options['sonata_field_description'];
    }

    public function configureOptions(OptionsResolver $resolver): void
    {
        parent::configureOptions($resolver);

        $dataClass = fn(Options $options) => $options['class'];

        $resolver->setDefaults(
            [
                'class' => null,
                'data_class' => $dataClass,
                'translation_domain' => 'messages',
                'horizontal' => true,
                'horizontal_input_wrapper_class' => 'col-md-12',
                'horizontal_label_offset_class' => '',
            ]
        );
    }

    /**
     * @param string $entity
     */
    protected function invokeCallbacks($entity, array $callbacks, array $args): void
    {
        if (count($callbacks) > 0) {
            $classReflection = new \ReflectionClass($entity);
            foreach ($callbacks as $methodName => $annotation) {
                $method = $classReflection->getMethod($methodName);
                $method->invokeArgs(null, $args);
            }
        }
    }

    public function getBlockPrefix(): string
    {
        return 'networking_type_content_block';
    }
}
