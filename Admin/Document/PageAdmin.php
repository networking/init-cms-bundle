<?php
/**
 * This file is part of the init_cms_sandbox package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Networking\InitCmsBundle\Admin\Document;

use Networking\InitCmsBundle\Admin\Model\PageAdmin as ModelPageAdmin;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Networking\InitCmsBundle\Model\PageInterface;
use Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery;

/**
 * Class PageAdmin
 * @package Networking\InitCmsBundle\Admin\Document
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class PageAdmin extends ModelPageAdmin
{


    /**
     * @param \Sonata\AdminBundle\Datagrid\DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add(
                'locale',
                'doctrine_mongo_callback',
                array(
                    'callback' => array(
                        $this,
                        'getByLocale'
                    )
                ),
                'choice',
                array(
                    'empty_value' => false,
                    'choices' => $this->getLocaleChoices(),
                    'preferred_choices' => array($this->getDefaultLocale())
                )
            )
            ->add('pageName', 'networking_init_cms_simple_string')
            ->add(
                'path',
                'doctrine_mongo_callback',
                array('callback' => array($this, 'matchPath'), 'hidden' => true)
            )
            ->add(
                'status',
                'doctrine_mongo_choice',
                array('hidden' => true),
                'sonata_type_translatable_choice',
                array(
                    'choices' => array(
                        PageInterface::STATUS_DRAFT => PageInterface::STATUS_DRAFT,
                        PageInterface::STATUS_REVIEW => PageInterface::STATUS_REVIEW,
                        PageInterface::STATUS_PUBLISHED => PageInterface::STATUS_PUBLISHED,
                    ),
                    'catalogue' => $this->translationDomain
                )
            );
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {

        $listMapper
            ->addIdentifier(
                'adminTitle',
                'string',
                array('template' => 'NetworkingInitCmsBundle:PageAdmin:page_title_list_field.html.twig')
            )
//                ->add('locale', null, array('sortable' => false))
            ->add(
                'status',
                null,
                array(
                    'label' => ' ',
                    'sortable' => false,
                    'template' => 'NetworkingInitCmsBundle:PageAdmin:page_status_list_field.html.twig'
                )
            )
            ->add(
                'fullPath',
                'string',
                array(
                    'sortable' => false,
                    'template' => 'NetworkingInitCmsBundle:PageAdmin:page_title_list_field.html.twig'
                )
            );

        $listMapper->add(
            '_action',
            'actions',
            array(
                'label' => ' ',
                'actions' => array(
                    'edit' => array(),
                    'delete' => array(),
                )
            )
        );
    }

    /**
     * @param \Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery $proxyQuery
     * @param $alias
     * @param $field
     * @param $data
     * @return bool
     */
    public function matchPath(ProxyQuery $proxyQuery, $alias, $field, $data)
    {
        $qb = $proxyQuery->getQueryBuilder();
        if (!$data || !is_array($data) || !array_key_exists('value', $data)) {
            return;
        }
        $data['value'] = trim($data['value']);

        if (strlen($data['value']) == 0) {
            return;
        }

        $fieldName = 'path';

        $qb->field($fieldName)->equals(new \MongoRegex(sprintf('/.*%s.*/i', $data['value'])));

        return true;
    }

    /**
     * @param \Sonata\DoctrineMongoDBAdminBundle\Datagrid\ProxyQuery $proxyQuery
     * @param $alias
     * @param $field
     * @param $data
     * @return bool
     */
    public function getByLocale($proxyQuery, $alias, $field, $data)
    {
        $qb = $proxyQuery->getQueryBuilder();

        $active = true;
        if (!$locale = $data['value']) {
            $locale = $this->getDefaultLocale();
            $active = false;
        }

        $qb->field('locale')->equals($locale);
        $qb->sort('path', 'asc');

        return $active;
    }


    public function getFormBuilder()
    {
        try {
            $request = $this->getRequest();
        } catch (\RuntimeException $e) {
            $request = $this->getContainer()->get('request');
        }

        $this->pageLocale = $request->get('locale') ? $request->get('locale') : $this->getSubject()->getLocale();

        if (!$this->pageLocale) {
            throw new \Symfony\Component\Form\Exception\InvalidArgumentException('Cannot create a page without a language');
        }

        /** @var $pageManager \Networking\InitCmsBundle\Model\PageManagerInterface */
        $pageManager = $this->getContainer()->get('networking_init_cms.page_manager');

        if ($this->getSubject()->getId()) {
            $this->pageLocale = $this->getSubject()->getLocale();
        }

        $validationGroups = array('default');

        $homePage = $pageManager->findOneBy(array('isHome' => true, 'locale' => $this->pageLocale));


        $this->canCreateHomepage = (!$homePage) ? true : false;

        if (!$this->canCreateHomepage) {
            if (!$this->getSubject()->getId() || !$this->getSubject()->isHome()) {
                $validationGroups[] = 'not_home';
            }
        }


        $this->formOptions['validation_groups'] = $validationGroups;

        return parent::getFormBuilder();

    }
}
 