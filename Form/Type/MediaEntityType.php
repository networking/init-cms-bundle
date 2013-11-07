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

use Doctrine\ORM\EntityRepository;
use Symfony\Bridge\Doctrine\Form\ChoiceList\EntityChoiceList;
use Symfony\Bridge\Doctrine\Form\ChoiceList\ORMQueryBuilderLoader;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\Form\FormView;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Doctrine\Common\Persistence\ObjectManager;

/**
 * Class MediaEntityType
 * @package Networking\InitCmsBundle\Form\Type
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class MediaEntityType extends EntityType
{
    /**
     * @var array
     */
    private $choiceListCache = array();

    /**
     * @var null
     */
    public $context = null;

    /**
     * @var null
     */
    public $providerName = null;

    /**
     * {@inheritdoc}
     */
    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->setAttribute('dataType', $options['dataType']);
    }

    /**
     * {@inheritdoc}
     */
    public function buildView(FormView $view, FormInterface $form, array $options)
    {
        $view->vars['objects'] = $options['choice_list']->getChoices();
        $view->vars['provider_name'] = $options['provider_name'];
    }

    /**
     * {@inheritdoc}
     */
    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {

        $choiceListCache =& $this->choiceListCache;
        $registry = $this->registry;
        $type = $this;

        $loader = function (Options $options) use ($type) {
            if (null !== $options['context'] || null !== $options['provider_name']) {
                $type->context = $options['context'];
                $type->providerName = $options['provider_name'];

                return $type->getLoader($options['em'], $options['query_builder'], $options['class']);
            }

            return null;
        };

        $choiceList = function (Options $options) use (&$choiceListCache, &$time) {
            // Support for closures
            $propertyHash = is_object($options['property'])
                ? spl_object_hash($options['property'])
                : $options['property'];

            $choiceHashes = $options['choices'];

            // Support for recursive arrays
            if (is_array($choiceHashes)) {
                // A second parameter ($key) is passed, so we cannot use
                // spl_object_hash() directly (which strictly requires
                // one parameter)
                array_walk_recursive(
                    $choiceHashes,
                    function (&$value) {
                        $value = spl_object_hash($value);
                    }
                );
            }

            $preferredChoiceHashes = $options['preferred_choices'];

            if (is_array($preferredChoiceHashes)) {
                array_walk_recursive(
                    $preferredChoiceHashes,
                    function (&$value) {
                        $value = spl_object_hash($value);
                    }
                );
            }

            // Support for custom loaders (with query builders)
            $loaderHash = is_object($options['loader'])
                ? spl_object_hash($options['loader'])
                : $options['loader'];

            // Support for closures
            $groupByHash = is_object($options['group_by'])
                ? spl_object_hash($options['group_by'])
                : $options['group_by'];

            $hash = md5(
                json_encode(
                    array(
                        spl_object_hash($options['em']),
                        $options['class'],
                        $propertyHash,
                        $loaderHash,
                        $choiceHashes,
                        $preferredChoiceHashes,
                        $groupByHash
                    )
                )
            );

            if (!isset($choiceListCache[$hash])) {
                $choiceListCache[$hash] = new EntityChoiceList(
                    $options['em'],
                    $options['class'],
                    $options['property'],
                    $options['loader'],
                    $options['choices'],
                    $options['preferred_choices'],
                    $options['group_by']
                );
            }

            return $choiceListCache[$hash];
        };

        $emNormalizer = function (Options $options, $em) use ($registry) {
            /* @var ManagerRegistry $registry */
            if (null !== $em) {
                return $registry->getManager($em);
            }

            $em = $registry->getManagerForClass($options['class']);

            if (null === $em) {
                throw new FormException(sprintf(
                    'Class "%s" seems not to be a managed Doctrine entity. ' .
                    'Did you forget to map it?',
                    $options['class']
                ));
            }

            return $em;
        };

        $resolver->setDefaults(
            array(
                'em' => null,
                'property' => null,
                'query_builder' => null,
                'loader' => $loader,
                'choices' => null,
                'choice_list' => $choiceList,
                'group_by' => null,
                'required' => false,
                'dataType' => 'entity',
                'expanded' => true,
                'provider_name' => false,
                'context' => false,
                'query_builder' => function (EntityRepository $er) {
                        $qb = $er->createQueryBuilder('m');

                        return $qb;
                    }
            )
        );

        $resolver->setRequired(array('class'));

        $resolver->setNormalizers(
            array(
                'em' => $emNormalizer,
            )
        );

        $resolver->setAllowedTypes(
            array(
                'loader' => array('null', 'Symfony\Bridge\Doctrine\Form\ChoiceList\EntityLoaderInterface'),
            )
        );


        $resolver->addAllowedValues(array('required' => array(false)));
    }

    /**
     * Return the default loader object.
     *
     * @param ObjectManager $manager
     * @param mixed $queryBuilder
     * @param string $class
     * @return ORMQueryBuilderLoader
     */
    public function getLoader(ObjectManager $manager, $queryBuilder, $class)
    {
        $type = $this;
        $queryBuilder = function (EntityRepository $er) use ($type) {
            $queryBuilder = $er->createQueryBuilder('m');
            if ($type->context) {
                $queryBuilder->andWhere('m.context = :context');
                $queryBuilder->setParameter('context', $type->context);
            }

            if ($type->providerName) {
                $queryBuilder->andWhere('m.providerName = :provider_name');
                $queryBuilder->setParameter('provider_name', $type->providerName);
            }

            return $queryBuilder;
        };

        return new ORMQueryBuilderLoader(
            $queryBuilder,
            $manager,
            $class
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'media_entity_type';
    }
}