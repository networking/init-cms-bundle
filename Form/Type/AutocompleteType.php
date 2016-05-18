<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Form\Type;

use Doctrine\Common\Persistence\ManagerRegistry;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\Form\ChoiceList\DoctrineChoiceLoader;
use Doctrine\ORM\Query\Parameter;
use Symfony\Bridge\Doctrine\Form\ChoiceList\IdReader;
use Doctrine\ORM\QueryBuilder;
use Networking\InitCmsBundle\Form\ORMQueryBuilderLoader;
use Symfony\Bridge\Doctrine\Form\Type\DoctrineType;
use Symfony\Component\Form\ChoiceList\Factory\CachingFactoryDecorator;
use Symfony\Component\Form\ChoiceList\Factory\ChoiceListFactoryInterface;
use Symfony\Component\Form\ChoiceList\Factory\DefaultChoiceListFactory;
use Symfony\Component\Form\Exception\UnexpectedTypeException;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\Form\Exception\RuntimeException;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\PropertyAccess\PropertyAccessorInterface;
use Symfony\Component\Form\ChoiceList\Factory\PropertyAccessDecorator;

/**
 * Class AutocompleteType
 * @package Networking\InitCmsBundle\Form\Type
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class AutocompleteType extends DoctrineType
{

    protected $hints;

    /**
     * @var ChoiceListFactoryInterface
     */
    private $choiceListFactory;

    /**
     * @var IdReader[]
     */
    private $idReaders = array();

    /**
     * @var DoctrineChoiceLoader[]
     */
    private $choiceLoaders = array();

    /**
     * AutocompleteType constructor.
     * @param ManagerRegistry $registry
     * @param PropertyAccessorInterface|null $propertyAccessor
     * @param ChoiceListFactoryInterface|null $choiceListFactory
     */
    public function __construct(ManagerRegistry $registry, PropertyAccessorInterface $propertyAccessor = null, ChoiceListFactoryInterface $choiceListFactory = null)
    {
        $this->registry = $registry;
        $this->choiceListFactory = $choiceListFactory ?: new CachingFactoryDecorator(
            new PropertyAccessDecorator(
                new DefaultChoiceListFactory(),
                $propertyAccessor
            )
        );
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {

        $registry = $this->registry;
        $choiceListFactory = $this->choiceListFactory;
        $idReaders = &$this->idReaders;
        $choiceLoaders = &$this->choiceLoaders;
        $type = $this;

        $choiceLoader = function (Options $options) use ($choiceListFactory, &$choiceLoaders, $type) {

            // Unless the choices are given explicitly, load them on demand
            if (null === $options['choices']) {
                $hash = null;
                $qbParts = null;

                // If there is no QueryBuilder we can safely cache DoctrineChoiceLoader,
                // also if concrete Type can return important QueryBuilder parts to generate
                // hash key we go for it as well
                if (!$options['query_builder'] || false !== ($qbParts = $type->getQueryBuilderPartsForCachingHash($options['query_builder']))) {
                    $hash = CachingFactoryDecorator::generateHash(array(
                        $options['em'],
                        $options['class'],
                        $qbParts,
                        $options['loader'],
                    ));

                    if (isset($choiceLoaders[$hash])) {
                        return $choiceLoaders[$hash];
                    }
                }

                if ($options['loader']) {
                    $entityLoader = $options['loader'];
                } elseif (null !== $options['query_builder']) {
                    $entityLoader = $type->getLoader($options['em'], $options['query_builder'], $options['class'],
                        $options['query_hints']);
                } else {
                    $queryBuilder = $options['em']->getRepository($options['class'])->createQueryBuilder('e');
                    $entityLoader = $type->getLoader($options['em'], $queryBuilder, $options['class'],
                        $options['query_hints']);
                }

                $doctrineChoiceLoader = new DoctrineChoiceLoader(
                    $choiceListFactory,
                    $options['em'],
                    $options['class'],
                    $options['id_reader'],
                    $entityLoader
                );

                if ($hash !== null) {
                    $choiceLoaders[$hash] = $doctrineChoiceLoader;
                }

                return $doctrineChoiceLoader;
            }
        };

        $choiceLabel = function (Options $options) {
            // BC with the "property" option
            if ($options['property']) {
                return $options['property'];
            }

            // BC: use __toString() by default
            return array(__CLASS__, 'createChoiceLabel');
        };

        $choiceName = function (Options $options) {
            /** @var IdReader $idReader */
            $idReader = $options['id_reader'];

            // If the object has a single-column, numeric ID, use that ID as
            // field name. We can only use numeric IDs as names, as we cannot
            // guarantee that a non-numeric ID contains a valid form name
            if ($idReader->isIntId()) {
                return array(__CLASS__, 'createChoiceName');
            }

            // Otherwise, an incrementing integer is used as name automatically
        };

        // The choices are always indexed by ID (see "choices" normalizer
        // and DoctrineChoiceLoader), unless the ID is composite. Then they
        // are indexed by an incrementing integer.
        // Use the ID/incrementing integer as choice value.
        $choiceValue = function (Options $options) {
            /** @var IdReader $idReader */
            $idReader = $options['id_reader'];

            // If the entity has a single-column ID, use that ID as value
            if ($idReader->isSingleId()) {
                return array($idReader, 'getIdValue');
            }

            // Otherwise, an incrementing integer is used as value automatically
        };

        $emNormalizer = function (Options $options, $em) use ($registry) {
            /* @var ManagerRegistry $registry */
            if (null !== $em) {
                if ($em instanceof ObjectManager) {
                    return $em;
                }

                return $registry->getManager($em);
            }

            $em = $registry->getManagerForClass($options['class']);

            if (null === $em) {
                throw new RuntimeException(sprintf(
                    'Class "%s" seems not to be a managed Doctrine entity. '.
                    'Did you forget to map it?',
                    $options['class']
                ));
            }

            return $em;
        };

        // deprecation note
        $propertyNormalizer = function (Options $options, $propertyName) {
            if ($propertyName) {
                @trigger_error('The "property" option is deprecated since version 2.7 and will be removed in 3.0. Use "choice_label" instead.', E_USER_DEPRECATED);
            }

            return $propertyName;
        };

        // Invoke the query builder closure so that we can cache choice lists
        // for equal query builders
        $queryBuilderNormalizer = function (Options $options, $queryBuilder) {
            if (is_callable($queryBuilder)) {
                $queryBuilder = call_user_func($queryBuilder, $options['em']->getRepository($options['class']));

                if (null !== $queryBuilder && !$queryBuilder instanceof QueryBuilder) {
                    throw new UnexpectedTypeException($queryBuilder, 'Doctrine\ORM\QueryBuilder');
                }
            }

            return $queryBuilder;
        };

        // deprecation note
        $loaderNormalizer = function (Options $options, $loader) {
            if ($loader) {
                @trigger_error('The "loader" option is deprecated since version 2.7 and will be removed in 3.0. Override getLoader() instead.', E_USER_DEPRECATED);
            }

            return $loader;
        };

        // Set the "id_reader" option via the normalizer. This option is not
        // supposed to be set by the user.
        $idReaderNormalizer = function (Options $options) use (&$idReaders) {
            $hash = CachingFactoryDecorator::generateHash(array(
                $options['em'],
                $options['class'],
            ));

            // The ID reader is a utility that is needed to read the object IDs
            // when generating the field values. The callback generating the
            // field values has no access to the object manager or the class
            // of the field, so we store that information in the reader.
            // The reader is cached so that two choice lists for the same class
            // (and hence with the same reader) can successfully be cached.
            if (!isset($idReaders[$hash])) {
                $classMetadata = $options['em']->getClassMetadata($options['class']);
                $idReaders[$hash] = new IdReader($options['em'], $classMetadata);
            }

            return $idReaders[$hash];
        };

        $resolver->setDefaults(array(
            'em' => null,
            'property' => null, // deprecated, use "choice_label"
            'query_builder' => null,
            'loader' => null, // deprecated, use "choice_loader"
            'choices' => null,
            'choices_as_values' => true,
            'choice_loader' => $choiceLoader,
            'choice_label' => $choiceLabel,
            'choice_name' => $choiceName,
            'choice_value' => $choiceValue,
            'id_reader' => null, // internal
            'choice_translation_domain' => false,
            'query_hints' => array()
        ));

        $resolver->setRequired(array('class'));

        $resolver->setNormalizer('em', $emNormalizer);
        $resolver->setNormalizer('property', $propertyNormalizer);
        $resolver->setNormalizer('query_builder', $queryBuilderNormalizer);
        $resolver->setNormalizer('loader', $loaderNormalizer);
        $resolver->setNormalizer('id_reader', $idReaderNormalizer);

        $resolver->setAllowedTypes('em', array('null', 'string', 'Doctrine\Common\Persistence\ObjectManager'));
        $resolver->setAllowedTypes('loader', array('null', 'Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface'));
        $resolver->setAllowedTypes('query_builder', array('null', 'callable', 'Doctrine\ORM\QueryBuilder'));
        $resolver->setAllowedTypes('query_hints', array('array'));
    }

    /**
     * We consider two query builders with an equal SQL string and
     * equal parameters to be equal.
     *
     * @param QueryBuilder $queryBuilder
     *
     * @return array
     *
     * @internal This method is public to be usable as callback. It should not
     *           be used in user code.
     */
    public function getQueryBuilderPartsForCachingHash($queryBuilder)
    {
        return array(
            $queryBuilder->getQuery()->getSQL(),
            array_map(array($this, 'parameterToArray'), $queryBuilder->getParameters()->toArray()),
        );
    }

    /**
     * Converts a query parameter to an array.
     *
     * @param Parameter $parameter The query parameter
     *
     * @return array The array representation of the parameter
     */
    private function parameterToArray(Parameter $parameter)
    {
        return array($parameter->getName(), $parameter->getType(), $parameter->getValue());
    }

    /**
     * Return the default loader object.
     *
     * @param ObjectManager $manager
     * @param QueryBuilder  $queryBuilder
     * @param string        $class
     *
     * @return ORMQueryBuilderLoader
     */
    public function getLoader(ObjectManager $manager, $queryBuilder, $class, $hints = array())
    {
        $loader = new ORMQueryBuilderLoader($queryBuilder, $manager, $class, $hints);

        foreach ($hints as $name => $value){
            $loader->setHint($name, $value);
        }

        return $loader;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return 'networking_type_autocomplete';
    }
}