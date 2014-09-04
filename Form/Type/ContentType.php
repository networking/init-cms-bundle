<?php
/**
 * This file is part of the ubs package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Networking\InitCmsBundle\Form\Type;

use Ibrows\Bundle\SonataAdminAnnotationBundle\Reader\SonataAdminAnnotationReaderInterface;
use Networking\InitCmsBundle\Form\DataTransformer\PageToNumberTransformer;
use Networking\InitCmsBundle\Model\LayoutBlockFormListener;
use Networking\InitCmsBundle\Model\LayoutBlockFormListenerInterface;
use Networking\InitCmsBundle\Model\PageManagerInterface;
use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;


/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class ContentType extends AbstractType
{

    /**
     * @var \Ibrows\Bundle\SonataAdminAnnotationBundle\Reader\SonataAdminAnnotationReaderInterface
     */
    protected $annotationReader;

    /**
     * @param SonataAdminAnnotationReaderInterface $annotationReader
     */
    public function __construct(
        SonataAdminAnnotationReaderInterface $annotationReader
    ) {

        $this->annotationReader = $annotationReader;

    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {

        $annotations = $this->annotationReader->getFormMapperAnnotations($options['class']);


        $defaultValue = null;

        foreach ($annotations as $propertyName => $annotation) {

            $fieldName = $annotation->getName() ? : $propertyName;
            $builder->add(
                $fieldName,
                $annotation->getType(),
                array_merge($annotation->getOptions(), array())
            );
        }
        $this->invokeCallbacks($options['class'], $this->annotationReader->getFormMapperCallbacks($options['class']), array($builder));
        $options['data_class'] = $options['class'];
    }


    /**
     * @param array $options
     *
     * @return \Sonata\AdminBundle\Admin\FieldDescriptionInterface
     *
     * @throws \RuntimeException
     */
    protected function getFieldDescription(array $options)
    {
        if (!isset($options['sonata_field_description'])) {
            throw new \RuntimeException('Please provide a valid `sonata_field_description` option');
        }

        return $options['sonata_field_description'];
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        parent::setDefaultOptions($resolver);

        $dataClass = function (Options $options)  {
//            return null;
            return $options['class'];
        };

        $resolver->setDefaults(
            array(
                'class' => null,
                'data_class' => $dataClass,
                'translation_domain' => 'messages',
                'horizontal' => true,
                'horizontal_input_wrapper_class' => 'col-md-12',
                'horizontal_label_offset_class' => '',
            )
        );

    }

    /**
     * @param string $entity
     * @param array $callbacks
     * @param array $args
     */
    protected function invokeCallbacks($entity, array $callbacks, array $args)
    {
        if(count($callbacks) > 0){
            $classReflection = new \ReflectionClass($entity);
            foreach($callbacks as $methodName => $annotation){
                $method = $classReflection->getMethod($methodName);
                $method->invokeArgs(null, $args);
            }
        }
    }

    public function getName()
    {
        return 'networking_type_content_block';
    }

}
 