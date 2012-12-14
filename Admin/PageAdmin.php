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
use Sonata\AdminBundle\Admin\AdminInterface;

use Networking\InitCmsBundle\Entity\Page;
use Networking\InitCmsBundle\Entity\PageRepository;
use Knp\Menu\ItemInterface as MenuItemInterface;

class PageAdmin extends BaseAdmin
{
	public $supportsPreviewMode = true;
    /**
     * @param \Sonata\AdminBundle\Route\RouteCollection $collection
     */
    protected function configureRoutes(RouteCollection $collection)
    {
        $collection->add('translatePage', 'translate_page/{id}/{locale}', array(), array('method' => 'POST'));
        $collection->add('updateFormFieldElement', 'update_form_fields', array(), array('method' => 'POST'));
        $collection->add('addLayoutBlock', 'add_layout_block', array(), array('method' => 'POST'));
	    $collection->add('uploadTextBlockImage', 'upload_text_block_image', array(), array('method' => 'POST'));
	    $collection->add('updateLayoutBlockSort', 'update_layout_block_sort', array(), array('method' => 'GET'));
	    $collection->add('deleteLayoutBlock', 'delete_layout_block', array(), array('method' => 'GET'));
    }

    /**
     * @param \Sonata\AdminBundle\Form\FormMapper $formMapper
     */
    protected function configureFormFields(FormMapper $formMapper)
    {
        $this->setFormTheme(array('NetworkingInitCmsBundle:Form:page_form_admin_fields.html.twig'));
		$translator = $this->getTranslator();
        try {
            $request = $this->getRequest();
        } catch (\RuntimeException $e) {
            $request = $this->container->get('request');
        }

        $locale = $request->getLocale();

        /** @var $repository PageRepository */
        $repository = $this->container->get('Doctrine')->getRepository('NetworkingInitCmsBundle:Page');

        if ( $id = $request->get('id')) {
            $page = $repository->find($id);
            $locale = $page->getLocale();
        }

        $isHomePage = $repository->findOneBy(array('isHome' => true, 'locale' => $locale));

        $isHomeReadOnly = (!$isHomePage || $isHomePage->getId() == $id) ? false : true;

        $formMapper->with(
			                $translator->trans('legend.page_content', array(), $this->translationDomain)
				        )
	                ->add('layoutBlock',
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
	                    ))
	                ->end();

        $formMapper
                ->with(
			        $translator->trans('legend.page_settings', array(), $this->translationDomain),
	                array('collapsed' => $id));

	    if(!$isHomeReadOnly || $isHomePage->getId() == $id){
		    $formMapper->add('isHome', null, array('read_only' => $isHomeReadOnly, 'disabled' => $isHomeReadOnly));
	    }

	    $formMapper
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
                ->add('url', null, array('required' => $isHomeReadOnly));


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
                ->add('metaKeyword', null, array('required' => true))
                ->add('metaDescription', null, array('required' => true))
                ->add('status',
	                    'sonata_type_translatable_choice',
	                    array(
		                    'choices' => Page::getStatusList(),
		                    'catalogue' => $this->translationDomain
	                    )
                    )
		        ->add('visibility',
                        'sonata_type_translatable_choice',
                        array(
                            'choices' => Page::getVisibilityList(),
	                        'catalogue' => $this->translationDomain
                        )
                    )
                ->add('activeFrom',
	                    'date',
	                    array(
                            'widget' => 'choice',
		                    'format' => 'd.MMM.y',
                            'years' => range(Date('Y'), 2010),
                            'input' => 'datetime'
                            )
                    )
                ->end();
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
		        return 'NetworkingInitCmsBundle:CRUD:page_show.html.twig';
		        break;
	        case 'preview':
		        return 'NetworkingInitCmsBundle:CRUD:page_preview.html.twig';
		        break;
	        case 'edit':
		        return 'NetworkingInitCmsBundle:CRUD:page_edit.html.twig';
		        break;
	        case 'preview':
		        return 'NetworkingInitCmsBundle:CRUD:page_preview.html.twig';
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
                ->addIdentifier('adminTitle')
                ->add('locale', null, array('sortable' => false))
                ->add('status', null, array('sortable' => false))
		        ->add('visibility', null, array('sortable' => false));

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
				    ->assertNotBlank()
				    ->assertMaxLength(array('limit' => 255))
			    ->end()
			    ->with('metaKeyword')
			        ->assertNotBlank()
			    ->end()
			    ->with('metaDescription')
				    ->assertNotBlank()
			    ->end();

	    if(!$object->getIsHome()){
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

        if(!$id = $this->getRequest()->get('id')) return;

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

	/**
	 * @return array
	 */
	public function getBatchActions()
	{
	    // retrieve the default (currently only the delete action) actions
	    $actions = parent::getBatchActions();

	    // check user permissions
	    if($this->hasRoute('edit') && $this->isGranted('EDIT') && $this->hasRoute('delete') && $this->isGranted('DELETE')){
	        $actions['publish']= array(
	            'label'            => $this->trans('action_publish', array(), $this->translationDomain),
	            'ask_confirmation' => true // If true, a confirmation will be asked before performing the action
	        );

	    }

	    return $actions;
	}

}
