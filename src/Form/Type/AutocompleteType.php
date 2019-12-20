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

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Symfony\Bridge\Doctrine\Form\ChoiceList\DoctrineChoiceLoader;
use Doctrine\ORM\Query\Parameter;
use Symfony\Bridge\Doctrine\Form\ChoiceList\IdReader;
use Doctrine\ORM\QueryBuilder;
use Networking\InitCmsBundle\Form\ChoiceList\ORMQueryBuilderLoader;
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
 * Class AutocompleteType.
 *
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
    private $idReaders = [];

    /**
     * @var DoctrineChoiceLoader[]
     */
    private $choiceLoaders = [];

    /**
     * AutocompleteType constructor.
     *
     * @param ManagerRegistry                 $registry
     * @param PropertyAccessorInterface|null  $propertyAccessor
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

        parent::__construct($registry);
    }

    /**
     * @param OptionsResolver $resolver
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $registry = $this->registry;

        $choiceLoader = function (Options $options)  {

            // Unless the choices are given explicitly, load them on demand
            if (null === $options['choices']) {
                $hash = null;
                $qbParts = null;

                // If there is no QueryBuilder we can safely cache DoctrineChoiceLoader,
                // also if concrete Type can return important QueryBuilder parts to generate
                // hash key we go for it as well
                if (!$options['query_builder'] || false !== ($qbParts = $this->getQueryBuilderForCachingHash($options['query_builder']))) {
                    $hash = self::generateHash([
                        $options['em'],
                        $options['class'],
                        $qbParts,
                        $options['loader'],
                    ]);

                    if (isset($this->choiceLoaders[$hash])) {
                        return $this->choiceLoaders[$hash];
                    }
                }

                if ($options['loader']) {
                    $entityLoader = $options['loader'];
                } elseif (null !== $options['query_builder']) {
                    $entityLoader = $this->getLoader($options['em'], $options['query_builder'], $options['class'],
                        $options['query_hints']);
                } else {
                    $queryBuilder = $options['em']->getRepository($options['class'])->createQueryBuilder('e');
                    $entityLoader = $this->getLoader($options['em'], $queryBuilder, $options['class'],
                        $options['query_hints']);
                }

                $doctrineChoiceLoader = new DoctrineChoiceLoader(
                    $options['em'],
                    $options['class'],
                    $options['id_reader'],
                    $entityLoader
                );

                if ($hash !== null) {
                    $this->choiceLoaders[$hash] = $doctrineChoiceLoader;
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
            return [__CLASS__, 'createChoiceLabel'];
        };

        $choiceName = function (Options $options) {
            /** @var IdReader $idReader */
            $idReader = $options['id_reader'];

            // If the object has a single-column, numeric ID, use that ID as
            // field name. We can only use numeric IDs as names, as we cannot
            // guarantee that a non-numeric ID contains a valid form name
            if ($idReader->isIntId()) {
                return [__CLASS__, 'createChoiceName'];
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
                return [$idReader, 'getIdValue'];
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
        $idReaderNormalizer = function (Options $options) {
            $hash = CachingFactoryDecorator::generateHash([
                $options['em'],
                $options['class'],
            ]);

            // The ID reader is a utility that is needed to read the object IDs
            // when generating the field values. The callback generating the
            // field values has no access to the object manager or the class
            // of the field, so we store that information in the reader.
            // The reader is cached so that two choice lists for the same class
            // (and hence with the same reader) can successfully be cached.
            if (!isset($this->idReaders[$hash])) {
                $classMetadata = $options['em']->getClassMetadata($options['class']);
                $this->idReaders[$hash] = new IdReader($options['em'], $classMetadata);
            }

            return $this->idReaders[$hash];
        };

        $resolver->setDefaults([
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
            'query_hints' => [],
            'select2' => true,
        ]);

        $resolver->setRequired(['class']);

        $resolver->setNormalizer('em', $emNormalizer);
        $resolver->setNormalizer('property', $propertyNormalizer);
        $resolver->setNormalizer('query_builder', $queryBuilderNormalizer);
        $resolver->setNormalizer('loader', $loaderNormalizer);
        $resolver->setNormalizer('id_reader', $idReaderNormalizer);

        $resolver->setAllowedTypes('em', ['null', 'string', 'Doctrine\Common\Persistence\ObjectManager']);
        $resolver->setAllowedTypes('loader', ['null', 'Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface']);
        $resolver->setAllowedTypes('query_builder', ['null', 'callable', 'Doctrine\ORM\QueryBuilder']);
        $resolver->setAllowedTypes('query_hints', ['array']);
    }

    /**
     * We consider two query builders with an equal SQL string and
     * equal parameters to be equal.
     *
     * @param QueryBuilder $queryBuilder
     *
     * @return array
     */
    private function getQueryBuilderForCachingHash($queryBuilder)
    {
        return [
            $queryBuilder->getQuery()->getSQL(),
            array_map([$this, 'parameterToArray'], $queryBuilder->getParameters()->toArray()),
        ];
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
        return [$parameter->getName(), $parameter->getType(), $parameter->getValue()];
    }

    /**
     * Return the default loader object.
     *
     * @param ObjectManager $manager
     * @param mixed         $queryBuilder
     * @param string        $class
     * @param array         $hints
     *
     * @return ORMQueryBuilderLoader|\Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface
     */
    public function getLoader(ObjectManager $manager, $queryBuilder, $class, $hints = [])
    {
        $loader = new ORMQueryBuilderLoader($queryBuilder, $manager, $class);

        foreach ($hints as $name => $value) {
            $loader->setHint($name, $value);
        }

        return $loader;
    }

    /**
     * Generates a SHA-256 hash for the given value.
     *
     * Optionally, a namespace string can be passed. Calling this method will
     * the same values, but different namespaces, will return different hashes.
     *
     * @param mixed  $value     The value to hash
     * @param string $namespace Optional. The namespace
     *
     * @return string The SHA-256 hash
     */
    public static function generateHash($value, $namespace = '')
    {
        if (is_object($value)) {
            $value = spl_object_hash($value);
        } elseif (is_array($value)) {
            array_walk_recursive($value, function (&$v) {
                if (is_object($v)) {
                    $v = spl_object_hash($v);
                }
            });
        }

        return hash('sha256', $namespace.':'.serialize($value));
    }

    /**
     * @return string
     */
    public function getBlockPrefix()
    {
        return 'networking_type_autocomplete';
    }
}
