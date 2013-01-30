<?php

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Admin;

use Networking\InitCmsBundle\Admin\BaseAdmin,
    Sonata\AdminBundle\Admin\Admin,
    Sonata\AdminBundle\Datagrid\ListMapper,
    Sonata\AdminBundle\Datagrid\DatagridMapper,
    Sonata\AdminBundle\Validator\ErrorElement,
    Sonata\AdminBundle\Form\FormMapper,
    Sonata\AdminBundle\Route\RouteCollection,
    Sonata\AdminBundle\Admin\AdminInterface,
    Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery,
    Networking\InitCmsBundle\Entity\Page,
    Networking\InitCmsBundle\Entity\PageRepository,
    Knp\Menu\ItemInterface as MenuItemInterface,
    Sonata\AdminBundle\Form\Extension\Field\Type\FormTypeFieldExtension;

/**
 *
 */
class PageAdmin extends BaseAdmin
{
    /**
     * @var string
     */
    protected $baseRoutePattern = 'cms/pages';

    /**
     * @var bool
     */
//    public $supportsPreviewMode = true;

    /**
     * @param \Sonata\AdminBundle\Route\RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('translatePage', 'translate_page/{id}/locale/{locale}', array(), array('method' => 'POST'));
        $collection->add('parentPageList', 'parent_page/', array(), array('method' => 'GET'));
        $collection->add('updateFormFieldElement', 'update_form_fields', array(), array('method' => 'POST'));
        $collection->add('addLayoutBlock', 'add_layout_block', array(), array('method' => 'POST'));
        $collection->add('updateLayoutBlockSort', 'update_layout_block_sort', array(), array('method' => 'GET'));
        $collection->add('deleteLayoutBlock', 'delete_layout_block', array(), array('method' => 'GET'));
        $collection->add('link', 'link/{id}/locale/{locale}', array(), array('method' => 'GET'));
        $collection->add('draft', 'draft/{id}', array(), array('method' => 'GET'));
        $collection->add('review', 'review/{id}', array(), array('method' => 'GET'));
        $collection->add('publish', 'publish/{id}', array(), array('method' => 'GET'));
        $collection->add('cancelDraft', 'cancel_draft/{id}', array(), array('method' => 'GET'));
        $collection->add(
            'unlink',
            'unlink/{id}/translation_id/{translationId}',
            array(),
            array('method' => 'GET|DELETE')
        );
    }

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $this->setFormTheme(array('NetworkingInitCmsBundle:Form:page_form_admin_fields.html.twig'));


        try {
            $request = $this->getRequest();
        } catch (\RuntimeException $e) {
            $request = $this->container->get('request');
        }

        $locale = $request->get('locale') ? $request->get('locale') : $this->getSubject()->getLocale();

        if(!$locale){
            throw new \Symfony\Component\Form\Exception\CreationException('Cannot create a page without a language');
        }

        /** @var $repository PageRepository */
        $repository = $this->container->get('Doctrine')->getRepository('NetworkingInitCmsBundle:Page');

        if ($id = $request->get('id')) {
            $page = $repository->find($id);
            $locale = $page->getLocale();
        }

        $isHomePage = $repository->findOneBy(array('isHome' => true, 'locale' => $locale));

        $isHomeReadOnly = (!$isHomePage || $isHomePage->getId() == $id) ? false : true;



        if (!$isHomePage) {
            $formMapper->add(
                'isHome',
                'sonata_type_boolean',
                array('expanded' => true, 'read_only' => $isHomeReadOnly, 'disabled' => $isHomeReadOnly)
            );
        }


        if ($id || $request->isXmlHttpRequest()) {
            $formMapper->with('page_content')
                ->add(
                'layoutBlock',
                'sonata_type_collection',
                array(
                    'required' => true,
                    'by_reference' => false
                ),
                array(
                    'edit' => 'inline',
                    'inline' => 'table',
                    'sortable' => 'sortOrder',
                    'no_label' => true
                )
            )
                ->end();
        }


        $formMapper->with('page_settings');

        if(!$this->getSubject()->getId()){
            $formMapper
                ->add(
                    'locale',
                    'choice',
                    array(
                        'choices' => $this->getLocaleChoices(),
                        'preferred_choices' => array($locale)
                        )
                    );

        }

        $formMapper->add('title');
        if ($isHomeReadOnly) {
            $formMapper
                ->add(
                'parent',
                'networking_type_autocomplete',
                array(
                    'attr' => array('style' => "width:220px"),
                    'property' => 'AdminTitle',
                    'class' => 'Networking\\InitCmsBundle\\Entity\\Page',
                    'required' => false,
                    'query_builder' => $repository->getParentPagesQuery($locale, $id),
                )
            );
        }
        $formMapper->add('url', null, array('required' => $isHomeReadOnly), array('display_method' => 'getFullPath'));

        $formMapper
            ->add(
            'visibility',
            'sonata_type_translatable_choice',
            array(
                'choices' => Page::getVisibilityList(),
                'catalogue' => $this->translationDomain
            )
        )
            ->add(
            'template',
            'networking_type_iconradio',
            array(
                'expanded' => true,
                'choices' => $this->getPageTemplates(),
            )
        )
            ->add('metaKeyword', null, array('required' => true))
            ->add('metaDescription', null, array('required' => true));

        // end of group: page_settings
        $formMapper
            ->end();

        $formMapper->setHelps(
            array(
                'url' => $this->translator->trans('url.helper.text', array(), $this->translationDomain)
            )
        );

//        $formMapper->getFormBuilder()->setAttributes(array('class' => 'span3'));
//        die;

//            ->add('activeFrom',
//            'date',
//            array(
//                'widget' => 'choice',
//                'format' => 'd.MMM.y',
//                'years' => range(Date('Y'), 2010),
//                'input' => 'datetime'
//            )
//        )
//                ->with(
//	                $translator->trans('legend.page_tags', array(), $this->translationDomain),
//	                array('collapsed' => true)
//                    )
//                ->add('tags', 'sonata_type_model', array('required' => false, 'expanded' => true, 'multiple' => true))
//                ->end();

    }

    /**
     * @param  string      $name
     * @return null|string
     */
    public function getTemplate($name)
    {

        switch ($name) {
            case 'show':
                return 'NetworkingInitCmsBundle:PageAdmin:page_show.html.twig';
                break;
            case 'preview':
                return 'NetworkingInitCmsBundle:PageAdmin:page_preview.html.twig';
                break;
            case 'edit':
                if ($this->getSubject()->getId()) {
                    return 'NetworkingInitCmsBundle:PageAdmin:page_edit.html.twig';
                } else {
                    return 'NetworkingInitCmsBundle:PageAdmin:page_create.html.twig';
                }
                break;
            case 'preview':
                return 'NetworkingInitCmsBundle:PageAdmin:page_preview.html.twig';
                break;
            case 'list':
                return 'NetworkingInitCmsBundle:PageAdmin:page_list.html.twig';
                break;
            default:
                return parent::getTemplate($name);
                break;
        }
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\DatagridMapper $datagridMapper
     */
    protected function configureDatagridFilters(DatagridMapper $datagridMapper)
    {
        $datagridMapper
            ->add(
            'locale',
            'doctrine_orm_callback',
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
            ->add('title', 'networking_init_cms_simple_string')
            ->add(
            'path',
            'doctrine_orm_callback',
            array('callback' => array($this, 'matchPath'))
        );

    }

    /**
     * @param \Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery $queryBuilder
     * @param $alias
     * @param $field
     * @param $data
     * @return bool
     */
    public function matchPath(ProxyQuery $queryBuilder, $alias, $field, $data)
    {
        if (!$data || !is_array($data) || !array_key_exists('value', $data)) {
            return;
        }
        $data['value'] = trim($data['value']);

        if (strlen($data['value']) == 0) {
            return;
        }

        $fieldName = 'path';

        $queryBuilder->leftJoin(sprintf('%s.contentRoute', $alias), 'c');
        $parameterName = sprintf('%s_%s', $fieldName, $queryBuilder->getUniqueParameterId());

        $queryBuilder->andWhere(sprintf('%s.%s LIKE :%s', 'c', $fieldName, $parameterName));
        $queryBuilder->setParameter(sprintf(':%s', $parameterName), '%' . $data['value'] . '%');

        return true;
    }

    /**
     * @param \Sonata\DoctrineORMAdminBundle\Datagrid\ProxyQuery $queryBuilder
     * @param $alias
     * @param $field
     * @param $data
     * @return bool
     */
    public function getByLocale($queryBuilder, $alias, $field, $data)
    {
        $active = true;
        if (!$locale = $data['value']) {
            $locale = $this->getDefaultLocale();
            $active = false;
        }
        $queryBuilder->andWhere(sprintf('%s.locale = :locale', $alias));
        $queryBuilder->orderBy(sprintf('%s.path', $alias), 'asc');
        $queryBuilder->setParameter(':locale', $locale);

        return $active;
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
            null,
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
                    'delete' => array()
                )
            )
        );
    }

    /**
     * @param \Sonata\AdminBundle\Validator\ErrorElement $errorElement
     * @param mixed                                      $object
     */
    public function validate(ErrorElement $errorElement, $object)
    {
        $errorElement
            ->with('title')
            ->assertNotBlank()
            ->assertMaxLength(array('limit' => 255))
            ->end()
            ->with('metaKeyword')
            ->assertNotBlank()
            ->end()
            ->with('metaDescription')
            ->assertNotBlank()
            ->end();
        $errorElement
            ->with('locale')
            ->assertNotNull(array())
            ->assertNotBlank()
            ->end();
        $errorElement
            ->with('template')
            ->assertNotNull(array())
            ->assertNotBlank()
            ->end();

        if (!$object->getIsHome()) {
            $errorElement
                ->with('url')
                ->assertNotBlank()
                ->assertMaxLength(array('limit' => 255))
                ->end();
        }
    }


    /**
     * @param \Knp\Menu\ItemInterface $menu
     * @param $action
     * @param AdminInterface $childAdmin
     */
    protected function configureSideMenu(MenuItemInterface $menu, $action, AdminInterface $childAdmin = null)
    {

        if (!in_array($action, array('edit'))) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;

        if (!$id = $this->getRequest()->get('id')) {
            return;
        }

        /** @var $repository PageRepository */
        $repository = $this->container->get('Doctrine')->getRepository('NetworkingInitCmsBundle:Page');

        /** @var $page Page */
        $page = $repository->find($id);

        $translatedLocales = $page->getAllTranslations();

        $originalLocale = $page->getLocale();
        foreach ($this->languages as $language) {

            if ($language['locale'] == $originalLocale) {
                continue;
            }

            if ($translatedLocales->containsKey($language['locale'])) {

                $page = $translatedLocales->get($language['locale']);
                $menu->addChild(
                    $this->trans('View %language% version', array('%language%' => $language['label'])),
                    array('uri' => $admin->generateUrl('edit', array('id' => $page->getId())))
                );

            } else {
                $menu->addChild(
                    $this->trans('Translate %language%', array('%language%' => $language['label'])),
                    array(
                        'uri' => $admin->generateUrl(
                            'translatePage',
                            array('id' => $id, 'locale' => $language['locale'])
                        )
                    )
                );
            }

        }
    }

    /**
     * @return \Doctrine\Common\Collections\ArrayCollection
     */
    public function getTranslationLanguages()
    {
        $translationLanguages = new \Doctrine\Common\Collections\ArrayCollection();

        if (!$id = $this->getRequest()->get('id')) {
            return;
        }

        /** @var $repository PageRepository */
        $repository = $this->container->get('Doctrine')->getRepository('NetworkingInitCmsBundle:Page');

        /** @var $page Page */
        $page = $repository->find($id);

        $translatedLocales = $page->getAllTranslations();

        $originalLocale = $page->getLocale();

        foreach ($this->languages as $language) {

            if ($language['locale'] == $originalLocale) {
                continue;
            }

            if ($translatedLocales->containsKey($language['locale'])) {
                $translationLanguages->set(
                    $language['locale'],
                    array(
                        'short_label' => $language['short_label'],
                        'translation' => $translatedLocales->get($language['locale'])
                    )
                );

            } else {
                $translationLanguages->set(
                    $language['locale'],
                    array(
                        'short_label' => $language['short_label'],
                        'translation' => false
                    )
                );
            }

        }

        return $translationLanguages;
    }

    /**
     * Get the page templates from the configuration and create an array to use in the
     * choice field in the admin form
     *
     * @return array
     */
    protected function getPageTemplates()
    {
        $choices = array();

        $templates = $this->container->getParameter('networking_init_cms.page.templates');
        foreach ($templates as $key => $template) {
            $choices[$key] = $template['name'];
        }

        return $choices;
    }

    protected function getPageTemplateIcons()
    {
        $icons = array();

        $templates = $this->container->getParameter('networking_init_cms.page.templates');
        foreach ($templates as $key => $template) {
            $icons[$key] = isset($template['icon']) ? $template['icon'] : '';
        }

        return $icons;
    }


    /**
     * @return array
     */
    public function getBatchActions()
    {
        return array();
        // retrieve the default (currently only the delete action) actions
        $actions = parent::getBatchActions();

        // check user permissions
        if ($this->hasRoute('edit') && $this->isGranted('EDIT') && $this->hasRoute('delete') && $this->isGranted(
            'DELETE'
        )
        ) {
            $actions['publish'] = array(
                'label' => $this->trans('action_publish', array(), $this->translationDomain),
                'ask_confirmation' => true // If true, a confirmation will be asked before performing the action
            );

        }

        return $actions;
    }

}
