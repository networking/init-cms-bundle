<?php
/**
 * This file is part of the init_cms_sandbox package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Networking\InitCmsBundle\Admin\Entity;

use Doctrine\ORM\Query;
use Ibrows\SonataTranslationBundle\Admin\ORMTranslationAdmin;
use Lexik\Bundle\TranslationBundle\Manager\TransUnitManagerInterface;
use Networking\InitCmsBundle\Filter\SimpleStringFilter;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Sonata\DoctrineORMAdminBundle\Filter\StringFilter;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Intl\Intl;

/**
 * Class TranslationAdmin
 * @package Networking\InitCmsBundle\Admin\Entity
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class TranslationAdmin extends AbstractAdmin
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


    /**
     * @param array $options
     */
    public function setEditableOptions(array $options)
    {
        $this->editableOptions = $options;
    }

    /**
     * @param TransUnitManagerInterface $translationManager
     */
    public function setTransUnitManager(TransUnitManagerInterface $translationManager)
    {
        $this->transUnitManager = $translationManager;
    }

    /**
     * @param array $managedLocales
     */
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
        return array_key_exists('nonTranslatedOnly', $this->getDefaultSelections()) && (bool) $this->defaultSelections['nonTranslatedOnly'];
    }

    /**
     * @param array $selections
     */
    public function setDefaultSelections(array $selections)
    {
        $this->defaultSelections = $selections;
    }

    /**
     * @param array $prefixes
     */
    public function setEmptyPrefixes(array $prefixes)
    {
        $this->emptyFieldPrefixes = $prefixes;
    }

    /**
     * Whether or not to persist the filters in the session
     *
     * @var boolean
     */
    protected $persistFilters = true;

    /**
     * Not exportable
     * @return array
     */
    public function getExportFormats()
    {
        return [];
    }

    /**
     * @return string
     */
    public function getIcon()
    {
        return 'fa-globe';
    }


    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $filter)
    {

        $filter
            ->add('key', StringFilter::class)
            ->add('translations.content',StringFilter::class)
            ->add(
                'domain',
                SimpleStringFilter::class,
                [],
                ChoiceType::class,
                [
                    'choices' => $this->getDomains(),
                    'empty_data' => true,
                    'placeholder' => $this->trans('translation.domain.all_choices', [], $this->getTranslationDomain())
                ]
            );
    }

    /**
     * @param ListMapper $list
     */
    protected function configureListFields(ListMapper $list)
    {
        $list
            ->add(
                'key',
                'string'
            )
            ->add('domain', 'string');

        foreach ($this->managedLocales as $locale) {

            if ($this->request) {
                $localeString = $this->request->getLocale();
            } else {
                $localeString = $locale;
            }
            $localeList = Intl::getLocaleBundle()->getLocaleNames(substr($localeString, 0, 2));

            $fieldDescription = $this->modelManager->getNewFieldDescriptionInstance($this->getClass(), $locale);
            $fieldDescription->setTemplate(
                '@NetworkingInitCms/CRUD/base_inline_translation_field.html.twig'
            );
            $fieldDescription->setOption('locale', $locale);
            $fieldDescription->setOption('editable', $this->editableOptions);
            $fieldDescription->setOption('label', $localeList[$locale]);
            $list->add($fieldDescription);
        }
    }


    /**
     * @return array
     */
    public function getFilterParameters()
    {
        $parameters = [];

        // build the values array
        if ($this->hasRequest()) {
            $filters = $this->request->query->get('filter', []);

            // if persisting filters, save filters to session, or pull them out of session if no new filters set
            if ($this->persistFilters) {
                if ($filters == [] && $this->request->query->get('filters') != 'reset') {
                    $filters = $this->request->getSession()->get($this->getCode() . '.filter.parameters', []);
                } else {
                    $this->request->getSession()->set($this->getCode() . '.filter.parameters', $filters);
                }
            }

            $parameters = array_merge(
                $this->getModelManager()->getDefaultSortValues($this->getClass()),
                $this->datagridValues,
                $filters
            );

            if (!$this->determinedPerPageValue($parameters['_per_page'])) {
                $parameters['_per_page'] = $this->maxPerPage;
            }

            // always force the parent value
            if ($this->isChild() && $this->getParentAssociationMapping()) {
                $parameters[$this->getParentAssociationMapping()] = [
                    'value' => $this->request->get(
                        $this->getParent()->getIdParameter()
                    )
                ];
            }
        }

        return $parameters;
    }


    /**
     * @return array
     */
    public function getDomains()
    {
        /** @var ProxyQuery $proxyQuery */
        $proxyQuery = $this->getModelManager()->createQuery($this->getClass(), 't');
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
     * @param \Ibrows\SonataTranslationBundle\Admin\unknown $name
     * @return \Ibrows\SonataTranslationBundle\Admin\multitype|string
     */
    public function getTemplate($name)
    {

        if ($name === 'list') {
            return '@NetworkingInitCms/TranslationAdmin/list.html.twig';
        }

        if ($name === 'edit') {
            return '@NetworkingInitCms/TranslationAdmin/edit.html.twig';
        }

        return $this->getTemplateRegistry()->getTemplate($name);

    }

    /**
     * @param RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection
            ->add('clear_cache')
            ->add('create_trans_unit');
    }

    /**
     * {@inheritdoc}
     */
    public function buildDatagrid()
    {
        if ($this->datagrid) {
            return;
        }

        $filterParameters = $this->getFilterParameters();

        // transform _sort_by from a string to a FieldDescriptionInterface for the datagrid.
        if (isset($filterParameters['locale']) && is_array($filterParameters['locale'])) {
            $this->filterLocales = array_key_exists('value', $filterParameters['locale']) ? $filterParameters['locale']['value'] : $this->managedLocales;
        }

        parent::buildDatagrid();
    }

    /**
     * @param FormMapper $form
     */
    protected function configureFormFields(FormMapper $form)
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
     * @return ContainerInterface
     */
    protected function getContainer()
    {
        return $this->getConfigurationPool()->getContainer();
    }

    /**
     * @return string
     */
    protected function getDefaultDomain()
    {
        return $this->getContainer()->getParameter('ibrows_sonata_translation.defaultDomain');
    }

    /**
     * {@inheritdoc}
     */
    public function getBatchActions()
    {
        $actions = parent::getBatchActions();
        $actions['download'] = [
            'label'            => 'batch.download',
            'ask_confirmation' => false,
            'translation_domain' => 'IbrowsSonataTranslationBundle'
        ];

        return $actions;
    }
}
 