<?php

declare(strict_types=1);

/**
 * This file is part of the init_cms_sandbox package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Admin;

use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Lexik\Bundle\TranslationBundle\Entity\TransUnit;
use Lexik\Bundle\TranslationBundle\Manager\TransUnitManagerInterface;
use Networking\InitCmsBundle\Filter\SimpleStringFilter;
use Sonata\AdminBundle\Datagrid\DatagridInterface;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\AdminBundle\Route\RouteCollectionInterface;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\StringFilter;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Intl\Locales;

/**
 * Class TranslationAdmin.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class TranslationAdmin extends BaseAdmin
{
    /**
     * @var array
     */
    protected $managedLocales;

    /**
     * @var TransUnitManagerInterface
     */
    protected $transUnitManager;
    /**
     * @var array
     */
    protected $editableOptions;

    /**
     * @var array
     */
    protected $defaultSelections = [];

    /**
     * @var array
     */
    protected $emptyFieldPrefixes = [];

    /**
     * @var array
     */
    protected $filterLocales = [];

    public function setEditableOptions(array $options)
    {
        $this->editableOptions = $options;
    }

    public function setTransUnitManager(
        TransUnitManagerInterface $translationManager
    ) {
        $this->transUnitManager = $translationManager;
    }

    public function setManagedLocales(array $managedLocales)
    {
        $this->managedLocales = $managedLocales;
    }

    /**
     * @return array
     */
    public function getEmptyFieldPrefixes()
    {
        return $this->emptyFieldPrefixes;
    }

    /**
     * @return array
     */
    public function getDefaultSelections()
    {
        return $this->defaultSelections;
    }

    /**
     * @return bool
     */
    public function getNonTranslatedOnly()
    {
        return array_key_exists(
            'nonTranslatedOnly',
            $this->getDefaultSelections()
        )
            && (bool) $this->defaultSelections['nonTranslatedOnly'];
    }

    public function setDefaultSelections(array $selections)
    {
        $this->defaultSelections = $selections;
    }

    public function setEmptyPrefixes(array $prefixes)
    {
        $this->emptyFieldPrefixes = $prefixes;
    }

    /**
     * Whether or not to persist the filters in the session.
     *
     * @var bool
     */
    protected $persistFilters = true;

    /**
     * Not exportable.
     */
    public function getExportFormats(): array
    {
        return [];
    }

    protected function configureDatagridFilters(DatagridMapper $filter): void
    {
        /** @var \Doctrine\ORM\EntityManager $em */
        $em = $this->getContainer()->get('doctrine')->getManagerForClass(
            \Lexik\Bundle\TranslationBundle\Entity\File::class
        );

        $domains = [];
        $domainsQueryResult = $em->createQueryBuilder()
            ->select('DISTINCT t.domain')->from(
                '\\'.\Lexik\Bundle\TranslationBundle\Entity\File::class,
                't'
            )
            ->getQuery()
            ->getResult(Query::HYDRATE_ARRAY);

        array_walk_recursive(
            $domainsQueryResult,
            function ($domain) use (&$domains) {
                $domains[$domain] = $domain;
            }
        );
        ksort($domains);

        $filter
            ->add(
                'key',
                StringFilter::class,
                [],
                [
                    'global_search' => false,
                    'field_options' => [
                        'row_attr' => ['class' => 'form-floating'],
                        'translation_domain' => $this->getTranslationDomain(),
                    ],
                ]
            )

            ->add(
                'translations.content',
                StringFilter::class,
                [],
                [
                    'global_search' => false,
                    'field_options' => [
                        'row_attr' => ['class' => 'form-floating'],
                        'translation_domain' => $this->getTranslationDomain(),
                    ],
                ]
            )
            ->add(
                'domain',
                SimpleStringFilter::class,
                [],
                [
                    'global_search' => false,
                    'field_type' => ChoiceType::class,
                    'field_options' => [
                        'row_attr' => ['class' => 'form-floating'],
                        'choices' => $this->getDomains(),
                        'placeholder' => 'translation.domain.all_choices',
                        'translation_domain' => $this->getTranslationDomain(),
                        'choice_translation_domain' => false,
                    ],
                ]
            );
    }

    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add(
                'key',
                'string'
            )
            ->add('domain', 'string');

        foreach ($this->managedLocales as $locale) {
            if ($this->hasRequest()) {
                $localeString = $this->getRequest()->getLocale();
            } else {
                $localeString = $locale;
            }
            $localeList = Locales::getNames(substr($localeString, 0, 2));
            $list->add($locale, 'string', [
                'route' => [
                    'name' => 'edit',
                    'parameters' => [],
                ],
                'virtual_field' => true,
                'locale' => $locale,
                'editable' => true,
                'label' => $localeList[$locale],
                'translation_domain' => false,
                'template' => '@NetworkingInitCms/CRUD/base_inline_translation_field.html.twig',
            ]);
        }
    }

    public function configureFilterParameters(array $parameters): array
    {
        // build the values array
        if ($this->hasRequest()) {
            $bag = $this->getRequest()->query;
            $filters = $bag->all('filter');

            if (isset($filters[DatagridInterface::PAGE])) {
                $filters[DatagridInterface::PAGE]
                    = (int) $filters[DatagridInterface::PAGE];
            }
            if (isset($filters[DatagridInterface::PER_PAGE])) {
                $filters[DatagridInterface::PER_PAGE]
                    = (int) $filters[DatagridInterface::PER_PAGE];
            }

            // if persisting filters, save filters to session, or pull them out of session if no new filters set
            if ($this->persistFilters) {
                if ([] == $filters
                    && 'reset' != $this->getRequest()->query->get('filters')
                ) {
                    $filters = $this->getRequest()->getSession()->get(
                        $this->getCode().'.filter.parameters',
                        []
                    );
                } else {
                    $this->getRequest()->getSession()->set(
                        $this->getCode().'.filter.parameters',
                        $filters
                    );
                }
            }

            $parameters = array_merge(
                $this->getDefaultSortValues(),
                $filters
            );

            if (
                !isset($parameters[DatagridInterface::PER_PAGE])
                || !$this->determinedPerPageValue(
                    $parameters[DatagridInterface::PER_PAGE]
                )
            ) {
                $parameters[DatagridInterface::PER_PAGE] = $this->getMaxPerPage(
                );
            }

            // always force the parent value
            if ($this->isChild() && $this->getParentAssociationMapping()) {
                $parameters[$this->getParentAssociationMapping()] = [
                    'value' => $this->getRequest()->get(
                        $this->getParent()->getIdParameter()
                    ),
                ];
            }
        }

        // transform _sort_by from a string to a FieldDescriptionInterface for the datagrid.
        if (isset($parameters['locale']) && is_array($parameters['locale'])) {
            $this->filterLocales = array_key_exists(
                'value',
                $parameters['locale']
            ) ? $parameters['locale']['value'] : $this->managedLocales;
        }

        return $parameters;
    }

    /**
     * @return array
     */
    public function getDomains()
    {
        /** @var ProxyQuery $proxyQuery */
        $proxyQuery = $this->getModelManager()->createQuery(
            $this->getClass(),
            't'
        );
        $qb = $proxyQuery->getQueryBuilder();

        $qb->select('DISTINCT t.domain');
        $qb->orderBy('t.domain', 'ASC');

        $result = $qb->getQuery()->execute();

        $choices = [];
        foreach ($result as $domain) {
            $choices[$domain['domain']] = $domain['domain'];
        }

        return $choices;
    }

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollectionInterface $collection
    ): void {
        $collection
            ->add('clear_cache')
            ->add('create_trans_unit');
    }

    protected function configureFormFields(FormMapper $form): void
    {
        $subject = $this->getSubject();

        if (null === $subject->getId()) {
            $subject->setDomain($this->getDefaultDomain());
        }

        $form
            ->add('key', TextType::class)
            ->add('domain', TextType::class);
    }

    /**
     * @return string
     */
    protected function getDefaultDomain()
    {
        return $this->getContainer()->getParameter(
            'networking_init_cms.defaultDomain'
        );
    }

    public function configureBatchActions(array $actions): array
    {
        $actions['download'] = [
            'label' => 'batch.download',
            'ask_confirmation' => false,
            'translation_domain' => 'TranslationAdmin',
        ];

        return $actions;
    }

    /**
     * @param string $alias
     */
    private function joinTranslations(
        ProxyQuery $queryBuilder,
        $alias,
        array $locales = null
    ) {
        $alreadyJoined = false;
        $joins = $queryBuilder->getDQLPart('join');
        if (array_key_exists($alias, $joins)) {
            $joins = $joins[$alias];
            foreach ($joins as $join) {
                if (strpos(
                    (string) $join->__toString(),
                    "$alias.translations "
                )
                ) {
                    $alreadyJoined = true;
                }
            }
        }
        if (!$alreadyJoined) {
            /* @var QueryBuilder $queryBuilder */
            if ($locales) {
                $queryBuilder->leftJoin(
                    sprintf('%s.translations', $alias),
                    'translations',
                    'WITH',
                    'translations.locale = :locales'
                );
                $queryBuilder->setParameter('locales', $locales);
            } else {
                $queryBuilder->leftJoin(
                    sprintf('%s.translations', $alias),
                    'translations'
                );
            }
        }
    }

    /**
     * @return array
     */
    private function formatLocales(array $locales)
    {
        $formattedLocales = [];
        array_walk_recursive(
            $locales,
            function ($language) use (&$formattedLocales) {
                $formattedLocales[$language] = $language;
            }
        );

        return $formattedLocales;
    }

    public function toString(object $object): string
    {
        if (method_exists($object, '__toString')
            && null !== $object->__toString()
        ) {
            return $object->__toString();
        }

        if (!$object instanceof TransUnit) {
            return '';
        }

        $translations = $object->getTranslations();

        $locale = $this->getDefaultLocale();
        if (!$locale) {
            $locale = $this->managedLocales[0];
        }
        foreach ($translations as $translation) {
            if ($translation->getLocale() == $locale) {
                return $translation->getContent();
            }
        }

        return $object->getKey();
    }
}
