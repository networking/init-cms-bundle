<?php

/*
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Admin;

use Networking\InitCmsBundle\Admin\BaseAdmin;

use Sonata\AdminBundle\Admin\Admin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Datagrid\DatagridMapper;
use Sonata\AdminBundle\Validator\ErrorElement;
use Sonata\AdminBundle\Form\FormMapper;
use Sonata\AdminBundle\Route\RouteCollection;

use Networking\InitCmsBundle\Entity\Page;
use Networking\InitCmsBundle\Entity\PageRepository;
use Knp\Menu\ItemInterface as MenuItemInterface;

class PageAdmin extends BaseAdmin
{
    /**
     * @param \Sonata\AdminBundle\Route\RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('translatePage', 'translate_page/{id}/{locale}', array(), array('method' => 'POST'));
        $collection->add('updateFormFieldElement', 'update_form_fields', array(), array('method' => 'POST'));
        $collection->add('addLayoutBlock', 'add_layout_block', array(), array('method' => 'POST'));
	    $collection->add('uploadTextBlockImage', 'upload_text_block_image', array(), array('method' => 'POST'));
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

        $locale = $request->getLocale();
        $id = $request->get('id');

        /** @var $repository PageRepository */
        $repository = $this->container->get('Doctrine')->getRepository('NetworkingInitCmsBundle:Page');

        if ($id) {
            $page = $repository->find($id);
            $locale = $page->getLocale();
        }

        $isHomePage = $repository->findOneBy(array('isHome' => true, 'locale' => $locale));

        $isHomeReadOnly = (!$isHomePage || $isHomePage->getId() == $id) ? false : true;

        $formMapper->with('Content')
                        ->add('layoutBlock',
                            'sonata_type_collection',
                            array(
                                'required' => true,
                                'by_reference' => false
                            ),
                            array(
                                'template' => 'NetworkingInitCmsBundle:CRUD:edit_orm_one_to_many_custom.html.twig',
                                'edit' => 'inline',
                                'inline' => 'table',
                                'sortable' => 'sortOrder',
                                'no_label' => true
                            ))
                        ->end();

        $formMapper
                ->with('Page Settings', array('collapsed' => $id))
                ->add('isHome', null, array('read_only' => $isHomeReadOnly, 'disabled' => $isHomeReadOnly))
                ->add('locale',
                    'choice',
                    array(
                        'choices' => $this->getLocaleChoices(),
                        'read_only' => $id,
                        'disabled' => $id,
                        'preferred_choices' => array($locale)
                    ))
                ->add('template',
                    'choice',
                    array(
                        'choices' => $this->getPageTemplates()
                    ))
                ->add('title')
                ->add('url', null, array('required' => $isHomeReadOnly))
                ->add('path', 'text', array('required' => false , 'label' => 'Path' , 'read_only' => true))
                ->add('navigationTitle')
                ->add('showInNavigation', null, array('required' => false));

        if ($isHomeReadOnly) {
            $formMapper
                ->add('parent',
                    'entity',
                    array(
                        'property' => 'AdminTitle',
                        'class' => 'Networking\\InitCmsBundle\\Entity\\Page',
                        'required' => false,
                        'query_builder' => $repository->getParentPages($locale, $id),
                    ));
        }
        $formMapper
                ->add('metaKeyword')
                ->add('metaDescription')
                ->add('isActive', null, array('required' => false))
                ->add('activeFrom')
                ->add('activeTill')
                ->end()
                ->with('Tags', array('collapsed' => true))
                ->add('tags', 'sonata_type_model', array('required' => false, 'expanded' => true, 'multiple' => true))
                ->end();

    }

    /**
     * @param  string      $name
     * @return null|string
     */
    public function getTemplate($name)
    {
        switch ($name) {
            case 'edit':
                return 'NetworkingInitCmsBundle:CRUD:page_edit.html.twig';
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
                ->add('title')
                ->add('slug')
                ->add('tags')
                ->add('locale',
                    'doctrine_orm_callback',
                    array('callback' => array($this, 'getByLocale')),
                    'choice',
                    array(
                        'empty_value' => false,
                        'choices' => $this->getLocaleChoices(),
                        'preferred_choices' => array($this->getRequest()->getLocale())
                    ));
    }

    /**
     * @param $queryBuilder
     * @param $alias
     * @param $field
     * @param $value
     * @return bool
     */
    public function getByLocale($queryBuilder, $alias, $field, $value)
    {
        if (!$locale = $value['value']) {
            $locale = $this->getRequest()->getLocale();
        }
        $queryBuilder->andWhere(sprintf('%s.locale = :locale', $alias));
        $queryBuilder->orderBy(sprintf('%s.path', $alias), 'asc');
        $queryBuilder->setParameter(':locale', $locale);

        return true;
    }

    /**
     * @param \Sonata\AdminBundle\Datagrid\ListMapper $listMapper
     */
    protected function configureListFields(ListMapper $listMapper)
    {

        $listMapper
                ->addIdentifier('adminTitle', null, array('label' => 'Page Title'))
                ->add('locale', null, array('sortable' => false))
                ->add('isActive', null, array('sortable' => false));

        $listMapper->add('_action', 'actions', array(
            'actions' => array(
                'view' => array(),
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
                ->assertMaxLength(array('limit' => 255))
                ->end();
    }

    /**
     * @param \Knp\Menu\ItemInterface $menu
     * @param $action
     * @param \Sonata\AdminBundle\Admin\Admin $childAdmin
     */
    protected function configureSideMenu(MenuItemInterface $menu, $action, Admin $childAdmin = null)
    {

        if (!in_array($action, array('edit'))) {
            return;
        }

        $admin = $this->isChild() ? $this->getParent() : $this;

        $id = $this->getRequest()->get('id');

        /** @var $repository PageRepository */
        $repository = $this->container->get('Doctrine')->getRepository('NetworkingInitCmsBundle:Page');

        /** @var $page Page */
        $page = $repository->find($id);

        $translatedLocales = $page->getAllTranslations();

        $originalLocale = $page->getLocale();
        foreach ($this->languages as $language) {

            if ($language['locale'] == $originalLocale) continue;

            if ($translatedLocales->containsKey($language['locale'])) {

                $page = $translatedLocales->get($language['locale']);
                $menu->addChild(
                    $this->trans('View %language% version', array('%language%' => $language['label'])),
                    array('uri' => $admin->generateUrl('edit', array('id' => $page->getId())))
                );

            } else {
                $menu->addChild(
                    $this->trans('Translate %language%', array('%language%' => $language['label'])),
                    array('uri' => $admin->generateUrl('translatePage', array('id' => $id, 'locale' => $language['locale'])))
                );
            }

        }
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

}
