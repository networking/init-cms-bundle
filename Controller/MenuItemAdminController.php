<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Controller;

use Doctrine\Common\Collections\ArrayCollection;
use Networking\InitCmsBundle\Entity\MenuItem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Networking\InitCmsBundle\Entity\MenuItemManager;

/**
 * Class MenuItemAdminController
 * @package Networking\InitCmsBundle\Controller
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class MenuItemAdminController extends CRUDController
{
    protected $isNewMenuItem = false;

    /**
     * @var string
     */
    protected $currentMenuLanguage = '';

    /**
     * {@inheritdoc}
     */
    public function listAction($pageId = false, $menuId = false, $ajaxTemplate = 'menu_tabs', $rootMenuId = null)
    {

        if (false === $this->admin->isGranted('LIST')) {
            throw new AccessDeniedException();
        }

        if ($this->getRequest()->get('page_id')) {
            $pageId = $this->getRequest()->get('page_id');
        }

        if ($this->getRequest()->get('menu_id')) {
            $menuId = $this->getRequest()->get('menu_id');
            if ($menuId) {
                $this->get('session')->set('MenuItem.last_edited', $menuId);
            }
        }


        $this->get('session')->set('admin/last_page_id', $pageId);

        if ($this->getRequest()->get('show_now_confirm_dialog')) {
            $user = $this->getUser();
            $user->setAdminSetting('menuAdmin.show_now_confirm_dialog', true);

            $em = $this->getDoctrine()->getManager();

            $em->persist($user);
            $em->flush();
        }

        $menus = array();

        /** @var MenuItemManager $menuItemManager */
        $menuItemManager = $this->get('networking_init_cms.menu_item_manager');


        /*
         * if the list was filtered or a new menu entry was posted,
         * use the submitted locale for list, else set the locale
         * to the users default within the availble CMS frontend languages
         */

        if ($menuId) {
            $menu = $menuItemManager->find($menuId);

            if ($menu) {
                $this->currentMenuLanguage = $menu->getLocale();
            }

        }
        if ($postArray = $this->getRequest()->get($this->admin->getUniqid())) {

            // posted locale
            if (array_key_exists('locale', $this->getRequest()->get($this->admin->getUniqid()))) {
                $this->currentMenuLanguage = $postArray['locale'];
            }
        } elseif ($filterParameters = $this->admin->getFilterParameters()) {

            // filter locale
            if (array_key_exists('locale', $filterParameters) && $filterParameters['locale']['value']) {
                $this->currentMenuLanguage = $filterParameters['locale']['value'];
            }
        }

        if (!$this->currentMenuLanguage) {
            // fallback to default
            $this->currentMenuLanguage = $this->admin->getDefaultLocale();
        }


        if (!$this->currentMenuLanguage) {
            throw new \Sonata\AdminBundle\Exception\NoValueException('No locale has been provided to generate a menu list');
        }

        //use one of the locale to filter the list of menu entries (see above
        $rootNodes = $menuItemManager->getRootNodesByLocale($this->currentMenuLanguage);

        $childOpen = function ($node) {
            return sprintf('<li class="table-row-style" id="listItem_%s">', $node['id']);
        };
        $admin = $this->admin;
        $controller = $this;
        /** @var ArrayCollection $menuAllItems */
        $menuAllItems = new ArrayCollection($menuItemManager->findAllJoinPage());
        $nodeDecorator = function ($node) use ($admin, $controller, $menuItemManager,  $menuAllItems) {

            $items = $menuAllItems->filter(function($menuItem)use($node){
                if($menuItem->getId() == $node['id']){
                    return $menuItem;
                }
            });
            $node = $items->first();
            return $controller->renderView(
                'NetworkingInitCmsBundle:MenuItemAdmin:menu_list_item.html.twig',
                array('admin' => $admin, 'node' => $node)
            );
        };

        foreach ($rootNodes as $rootNode) {


            if ($rootMenuId) {
                if ($rootMenuId == $rootNode->getId()) {

                    $menus = array(
                        'rootNode' => $rootNode,
                        'navigation' => $this->createPlacementNavigation(
                                $rootNode,
                                $admin,
                                $controller,
                                $menuItemManager
                            ),
                    );
                    break;
                }
            } else {
                $navigation = $menuItemManager->childrenHierarchy(
                    $rootNode,
                    null,
                    array(
                        'rootOpen' => '<ul class="table-row-style">',
                        'decorate' => true,
                        'childOpen' => $childOpen,
                        'childClose' => '</li>',
                        'nodeDecorator' => $nodeDecorator
                    ),
                    false
                );
                $menus[] = array(
                    'rootNode' => $rootNode,
                    'navigation' => $navigation
                );
            }


        }


        $datagrid = $this->admin->getDatagrid();


        if ($menuId) {
            $menu = $menuItemManager->find($menuId);

            if ($menu) {
                $locale = $menu->getLocale();
                $datagrid->setValue('locale', null, $locale);
                $datagrid->buildPager();
            }

        }

        $formView = $datagrid->getForm()->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('form')->renderer->setTheme($formView, $this->admin->getFilterTheme());

        if ($this->isXmlHttpRequest()) {
            if ($this->getRequest()->get('render')) {
                return $this->render(
                    'NetworkingInitCmsBundle:MenuItemAdmin:' . $ajaxTemplate . '.html.twig',
                    array(
                        'menus' => $menus,
                        'admin' => $this->admin,
                        'page_id' => $pageId
                    )
                );
            } else {
                return $this->renderView(
                    'NetworkingInitCmsBundle:MenuItemAdmin:' . $ajaxTemplate . '.html.twig',
                    array(
                        'menus' => $menus,
                        'admin' => $this->admin,
                        'page_id' => $pageId
                    )
                );
            }
        }


        $lastEdited = $this->get('session')->get('MenuItem.last_edited');

        return $this->render(
            $this->admin->getTemplate('list'),
            array(
                'action' => 'navigation',
                'form' => $formView,
                'datagrid' => $datagrid,
                'menus' => $menus,
                'last_edited' => $lastEdited,
                'page_id' => $pageId,
                'menu_id' => $menuId
            )
        );
    }

    /**
     * {@inheritdoc}
     */
    public function createAction()
    {

        if ($this->getRequest()->get('subclass') && $this->getRequest()->get('subclass') == 'menu') {
            if (false === $this->admin->isGranted('ROLE_SUPER_ADMIN')) {
                throw new AccessDeniedException();
            }
        }

        if ($this->getRequest()->get('subclass') == 'menu item') {
            $this->isNewMenuItem = true;
            if ($this->getRequest()->get('root_id')) {
                $this->get('session')->set('root_menu_id', $this->getRequest()->get('root_id'));
            }
        }

        return parent::createAction();
    }

    /**
     * {@inheritdoc}
     */
    public function editAction($id = null)
    {

        if ($this->getRequest()->get('subclass') && $this->getRequest()->get('subclass') == 'menu') {
            if (false === $this->admin->isGranted('ROLE_SUPER_ADMIN')) {
                throw new AccessDeniedException();
            }
        }

        return parent::editAction($id);
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAction($id)
    {
        $id = $this->get('request')->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('DELETE', $object)) {
            throw new AccessDeniedException();
        }

        if ($this->getRequest()->get('subclass') && $this->getRequest()->get('subclass') == 'menu') {
            if (false === $this->admin->isGranted('ROLE_SUPER_ADMIN')) {
                throw new AccessDeniedException();
            }
        }

        if ($this->getRequest()->getMethod() == 'DELETE') {
            try {
                $this->currentMenuLanguage = $object->getLocale();
                $this->admin->delete($object);

                if ($this->isXmlHttpRequest()) {
                    return $this->renderJson(
                        array(
                            'result' => 'ok',
                            'objectId' => $this->admin->getNormalizedIdentifier($object),
                            'status' => 'success',
                            'message' => $this->admin->trans('flash_delete_success')
                        )
                    );
                } else {
                    $this->get('session')->getFlashBag()->add('sonata_flash_success', 'flash_delete_success');
                }
            } catch (ModelManagerException $e) {
                if ($this->isXmlHttpRequest()) {
                    return $this->renderJson(
                        array(
                            'result' => 'ok',
                            'objectId' => $this->admin->getNormalizedIdentifier($object),
                            'status' => 'error',
                            'message' => $this->admin->trans($e->getMessage())
                        )
                    );
                } else {
                    $this->get('session')->getFlashBag()->add('sonata_flash_error', 'flash_delete_error');
                }

            }

            return new RedirectResponse($this->admin->generateUrl('list'));
        }

        return $this->render(
            $this->admin->getTemplate('delete'),
            array(
                'object' => $object,
                'action' => 'delete'
            )
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param $rootId
     * @param $pageId
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function createFromPageAction(Request $request, $rootId, $pageId)
    {
        $rootNode = $this->admin->getObject($rootId);

        if (!$rootNode) {
            throw new NotFoundHttpException(sprintf('unable to find the Menu with id : %s', $rootId));
        }

        $page = $this->get('networking_init_cms.page_manager')->find($pageId);

        if (!$page) {
            throw new NotFoundHttpException(sprintf('unable to find the Page with id : %s', $pageId));
        }

        $menuItem = new MenuItem();

        $menuItem->setPage($page);
        $menuItem->setRoot($rootNode);
        $menuItem->setParent($rootNode);
        $menuItem->setName($page->getTitle());

        $em = $this->getDoctrine()->getManager();
        $em->persist($menuItem);
        $em->flush();

        $this->admin->createObjectSecurity($menuItem);

        return $this->redirect(
            $this->admin->generateUrl('list', array('page_id' => $page->getId(), 'menu_id' => $menuItem->getId())).'#end'
        );

    }

    /**
     * @param $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function ajaxControllerAction(Request $request)
    {
        $operation = $request->request->get('operation') ? $request->request->get('operation') : $request->query->get(
            'operation'
        );

        return new JsonResponse($this->$operation());
    }

    /**
     * @return array
     */
    public function updateNodes()
    {
        $request = $this->getRequest();
        $nodes = $request->get('nodes') ? $request->get('nodes') : array();

        try {
            foreach ($nodes as $node) {
                /** @var $menuItem MenuItem */
                $menuItem = $this->admin->getObject($node['item_id']);
                if ($node['parent_id']) {
                    $parent = $this->admin->getObject($node['parent_id']);
                    $menuItem->setParent($parent);
                } else {
                    ;
                    $menuItem->setParent($menuItem->getParentByLevel(0));
                }

                $menuItem->setLft($node['left']);
                $menuItem->setRgt($node['right']);
                $menuItem->setLvl($node['depth']);
                $this->admin->update($menuItem);
            }

            $response = array('status' => 'ok', 'message' => $this->admin->trans('info.menu_sorted'));
        } catch (\Exception $e) {
            $response = array('status' => 'error', 'message' => $this->admin->trans('info.menu_sorted_error'));
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function renderJson($data, $status = 200, $headers = array())
    {

        $response = parent::renderJson($data, $status, $headers);

        $data = json_decode($response->getContent(), true);


        if (!array_key_exists('message', $data)) {
            //getFlashBag()->get('notice'
            if ($message = $this->get('session')->getFlashBag()->get('sonata_flash_success')) {
                $data['is_new_menu_item'] = $this->isNewMenuItem;
                $data['status'] = 'success';
            } elseif ($message = $this->get('session')->getFlashBag()->get('sonata_flash_error')) {
                $data['status'] = 'error';
            } elseif ($data['result'] == 'ok') {
                $this->getRequest()->request->all();
                $message = 'flash_' . str_replace('Action', '', $this->getCaller()) . '_success';
                $data['is_new_menu_item'] = $this->isNewMenuItem;

                if ($this->isNewMenuItem) {
                    $data['html'] = $this->placementAction();
                }
                $data['status'] = 'success';
            } else {
                $this->getRequest()->request->all();
                $message = 'flash_' . str_replace('Action', '', $this->getCaller()) . '_error';
                $data['status'] = 'error';
            }

            if ($message) {
                $data['message'] = $this->admin->trans($message);
            }
        }

        if (!array_key_exists('html', $data)) {
            $data['html'] = $this->listAction($this->get('session')->get('admin/last_page_id'));
        }

        if ($response instanceof JsonResponse) {
            $response->setData($data);
        } else {
            $response->setContent(json_encode($data));
        }

        return $response;
    }

    /**
     * renders the template html for the modal
     *
     * @return bool|string|Response
     * @throws \Sonata\AdminBundle\Exception\NoValueException
     */
    public function placementAction()
    {
        /** @var \Networking\InitCmsBundle\Entity\MenuItem $rootNode */
        $rootNode = $this->admin->getObject($this->get('session')->get('root_menu_id'));

        if($rootNode->getChildren()->count() > 1){
            return $this->listAction(null, null, 'placement', $this->get('session')->get('root_menu_id'));
        }else{
            return false;
        }

    }

    /**
     * @return string
     */
    public function getCaller()
    {
        $trace = debug_backtrace();
        $name = $trace[2]['function'];

        return empty($name) ? 'global' : $name;
    }

    /**
     * @param $rootNode
     * @param $admin
     * @param $controller
     * @param $menuItemManager
     * @return mixed
     */
    public function createPlacementNavigation($rootNode, $admin, $controller, $menuItemManager)
    {
        $lastEdited = $this->get('session')->get('MenuItem.last_edited');

        $nodeDecorator = function ($node) use ($admin, $controller, $menuItemManager, $lastEdited) {

            if ($lastEdited == $node['id']) {
                return;
            }
            $node = $menuItemManager->find($node['id']);

            return $controller->renderView(
                'NetworkingInitCmsBundle:MenuItemAdmin:placement_item.html.twig',
                array('admin' => $admin, 'last_edited' => $lastEdited, 'node' => $node)
            );
        };

        $childOpen = function ($node) use ($lastEdited) {
            if ($lastEdited == $node['id']) {
                return;
            }

            return sprintf(
                '<li class="table-row-style list_item" id="listItem_%s">',
                $node['id']
            );
        };

        $childClose = function ($node) use ($lastEdited, $admin, $controller) {
            if ($lastEdited == $node['id']) {
                return;
            }

            return $controller->renderView(
                'NetworkingInitCmsBundle:MenuItemAdmin:placement_item_end.html.twig',
                array('admin' => $admin, 'last_edited' => $lastEdited, 'node' => $node)
            );
        };


        $rootOpen = function($tree){
           $node = $tree[0];
                if($node['lvl'] == 1){
                    $class = 'ui-sortable';
                }else{
                    $class =  'table-row-style';
                }
           return sprintf('<ul class="%s">', $class);
        };

        $navigation = $menuItemManager->childrenHierarchy(
            $rootNode,
            null,
            array(
                'rootOpen' => $rootOpen,
                'decorate' => true,
                'childOpen' => $childOpen,
                'childClose' => $childClose,
                'nodeDecorator' => $nodeDecorator
            ),
            false
        );

        return $navigation;
    }

    /**
     * @param Request $request
     * @param $newMenuItemId
     * @param $menuItemId
     * @return Response
     */
    public function newPlacementAction(Request $request, $newMenuItemId, $menuItemId)
    {
        $sibling = $request->get('sibling');

        /** @var MenuItemManager $menuItemManager */
        $menuItemManager = $this->get('networking_init_cms.menu_item_manager');
        $newMenuItem = $menuItemManager->find($newMenuItemId);
        $menuItem = $menuItemManager->find($menuItemId);
        $data = array();
        try {
            if ($sibling) {
                $menuItemManager->persistAsNextSiblingOf($newMenuItem, $menuItem);
            } else {
                $menuItemManager->persistAsFirstChildOf($newMenuItem, $menuItem);
            }
            $this->getDoctrine()->getManager()->flush();
            $data = array(
                'result' => 'ok',
                'objectId' => $this->admin->getNormalizedIdentifier($newMenuItem)
            );

            return $this->renderJson($data);
        } catch (\Exception $e) {
            return $this->renderJson($data);
        }

    }


}
