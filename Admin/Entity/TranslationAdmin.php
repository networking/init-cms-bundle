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

use Ibrows\SonataTranslationBundle\Admin\ORMTranslationAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery;

/**
 * Class TranslationAdmin
 * @package Networking\InitCmsBundle\Admin\Entity
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class TranslationAdmin extends ORMTranslationAdmin
{

    /**
     * Whether or not to persist the filters in the session
     *
     * @var boolean
     */
    protected $persistFilters = true;

    /**
     * {@inheritdoc}
     */
    protected function configureDatagridFilters(DatagridMapper $filter)
    {
        $filter
            ->add('key', 'doctrine_orm_string')
            ->add('translations.content', 'doctrine_orm_string')
            ->add(
                'domain',
                'doctrine_orm_choice',
                array(),
                'choice',
                array(
                    'choices' => $this->getDomains(),
                    'empty_data' => true,
                    'empty_value' => $this->trans('translation.domain.all_choices', array(), $this->getTranslationDomain())
                )
            );
    }

    /**
     * @param ListMapper $list
     */
    protected function configureListFields(ListMapper $list)
    {
        $list
            ->addIdentifier(
                'key',
                'string',
                array('template' => 'SandboxInitCmsBundle:CRUD:translation_key_field.html.twig')
            )
            ->add('domain', 'string');

        foreach ($this->managedLocales as $locale) {
            $fieldDescription = $this->modelManager->getNewFieldDescriptionInstance($this->getClass(), $locale);
            $fieldDescription->setTemplate(
                'IbrowsSonataTranslationBundle:CRUD:base_inline_translation_field.html.twig'
            );
            $fieldDescription->setOption('locale', $locale);
            $fieldDescription->setOption('editable', $this->editableOptions);
            $list->add($fieldDescription);
        }
    }


    /**
     * @return array
     */
    public function getFilterParameters()
    {
        $parameters = array();

        // build the values array
        if ($this->hasRequest()) {
            $filters = $this->request->query->get('filter', array());

            // if persisting filters, save filters to session, or pull them out of session if no new filters set
            if ($this->persistFilters) {
                if ($filters == array() && $this->request->query->get('filters') != 'reset') {
                    $filters = $this->request->getSession()->get($this->getCode() . '.filter.parameters', array());
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
                $parameters[$this->getParentAssociationMapping()] = array(
                    'value' => $this->request->get(
                            $this->getParent()->getIdParameter()
                        )
                );
            }
        }

        return $parameters;
    }

    /**
     * @param string $name
     * @return multitype|NULL
     */
    public function getTemplate($name)
    {
        if ($name === 'layout') {
            return 'IbrowsSonataTranslationBundle::translation_layout.html.twig';
        }

        if ($name === 'list') {
            return 'NetworkingInitCmsBundle:TranslationAdmin:list.html.twig';
        }

        return parent::getTemplate($name);
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

        $choices = array();
        foreach ($result as $domain) {
            $choices[$domain['domain']] = $domain['domain'];
        }

        return $choices;
    }
}
 