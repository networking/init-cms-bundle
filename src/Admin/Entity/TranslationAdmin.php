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
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;
use Symfony\Component\Form\Extension\Core\Type\CheckboxType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Intl\Intl;

/**
 * Class TranslationAdmin
 * @package Networking\InitCmsBundle\Admin\Entity
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class TranslationAdmin extends ORMTranslationAdmin
{
    /**
     * @var array
     */
    protected $managedLocales;

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
            ->add('key', 'doctrine_orm_string')
            ->add(
                'translations.content',
                'doctrine_orm_string'
            )
            ->add(
                'domain',
                'doctrine_orm_choice',
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
                'NetworkingInitCmsBundle:CRUD:base_inline_translation_field.html.twig'
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
        if ($name === 'layout') {

            return $this->getOriginalTemplate('layout');
        }

        if ($name === 'list') {
            return 'NetworkingInitCmsBundle:TranslationAdmin:list.html.twig';
        }

        if ($name === 'edit') {
            return 'NetworkingInitCmsBundle:TranslationAdmin:edit.html.twig';
        }

        return parent::getTemplate($name);
    }
}
 