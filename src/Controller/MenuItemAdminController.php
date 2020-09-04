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

use Networking\InitCmsBundle\Admin\Entity\PageAdmin;
use Networking\InitCmsBundle\Cache\PageCacheInterface;
use Networking\InitCmsBundle\Component\EventDispatcher\CmsEventDispatcher;
use Networking\InitCmsBundle\Entity\BaseMenuItem;
use Networking\InitCmsBundle\Entity\MenuItem;
use Networking\InitCmsBundle\Model\MenuItemManagerInterface;
use Networking\InitCmsBundle\Model\PageManagerInterface;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class MenuItemAdminController.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class MenuItemAdminController extends CRUDController
{
    /**
     * @var MenuItemManagerInterface
     */
    protected $menuItemManager;

    /**
     * @var PageManagerInterface
     */
    protected $pageManager;

    /**
     * @var bool
     */
    protected $isNewMenuItem = false;

    /**
     * @var string
     */
    protected $currentMenuLanguage = '';

    protected $caller;


    /**
     * MenuItemAdminController constructor.
     * @param MenuItemManagerInterface $menuItemManager
     * @param CmsEventDispatcher $dispatcher
     * @param PageCacheInterface $pageCache
     * @param PageManagerInterface $pageManager
     */
    public function __construct(
        MenuItemManagerInterface $menuItemManager,
        CmsEventDispatcher $dispatcher,
        PageCacheInterface $pageCache,
        PageManagerInterface $pageManager
    ) {
        $this->menuItemManager = $menuItemManager;
        $this->pageManager = $pageManager;
        parent::__construct($dispatcher, $pageCache);
    }

    /**
     * {@inheritdoc}
     */
    public function listAction($pageId = false, $menuId = false, $ajaxTemplate = 'menu_tabs', $rootMenuId = null)
    {
        $this->caller = __FUNCTION__;
        if (false === $this->admin->isGranted('LIST')) {
            throw new AccessDeniedException();
        }

        /** @var Request $request */
        $request = $this->getRequest();

        if ($request->get('page_id')) {
            $pageId = $request->get('page_id');
        }

        if ($menuId = $request->get('menu_id', false)) {
            $this->get('session')->set('MenuItem.last_edited', $menuId);
            $menu = $this->menuItemManager->find($menuId);
            if ($menu) {
                $this->currentMenuLanguage = $menu->getLocale();
            }
        }

        $request->getSession()->set('admin/last_page_id', $pageId);

        if ($request->get('show_now_confirm_dialog')) {
            $user = $this->getUser();
            $user->setAdminSetting('menuAdmin.show_now_confirm_dialog', true);

            $em = $this->getDoctrine()->getManager();

            $em->persist($user);
            $em->flush();
        }


        if ($postArray = $request->get($this->admin->getUniqid())) {

            // posted locale
            if (array_key_exists('locale', $request->get($this->admin->getUniqid()))) {
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
            throw new \Sonata\AdminBundle\Exception\NoValueException(
                'No locale has been provided to generate a menu list'
            );
        }

        $selected = $request->getSession()->get('MenuItem.last_edited');
        $menus = [];
        /** @var MenuItem[] $rootNodes */
        $rootNodes = $this->menuItemManager->getRootNodesByLocale($this->currentMenuLanguage, 'id');
        foreach ($rootNodes as $rootNode) {
            $children = $this->menuItemManager->getChildren($rootNode, true);
            $menus[] = [
                'rootNode' => $rootNode,
                'navigation' => $this->addMenuBranch($children, $selected, []),
            ];
        }

        if ($this->isXmlHttpRequest()) {
            $template = '@NetworkingInitCms/MenuItemAdmin/menu_tabs.html.twig';
            $params = ['menus' => $menus, 'admin' => $this->admin, 'page_id' => $pageId];
            if ($request->get('render')) {
                return $this->renderWithExtraParams($template, $params);
            }

            return $this->renderView($template, $params);
        }


        $datagrid = $this->admin->getDatagrid();
        $datagrid->setValue('locale', null, $this->currentMenuLanguage);
        $datagrid->buildPager();

        $formView = $datagrid->getForm()->createView();

        // set the theme for the current Admin Form
        $this->setFormTheme($formView, $this->admin->getFilterTheme());

        return $this->renderWithExtraParams(
            $this->admin->getTemplate('list'),
            [
                'action' => 'navigation',
                'form' => $formView,
                'datagrid' => $datagrid,
                'menus' => $menus,
                'page_id' => $pageId,
                'menu_id' => $menuId,
            ]
        );
    }

    /**
     * @param MenuItem[] $nodes
     * @param $selected
     * @param $nodeArray
     * @return mixed
     */
    private function addMenuBranch($nodes, $selected, $nodeArray)
    {
        /** @var PageAdmin $pageAdmin */
        $pageAdmin = $this->container->get('sonata.admin.pool')->getAdminByAdminCode('networking_init_cms.admin.page');
        foreach ($nodes as $node) {
            $item = [
                'text' => $node->getName(),
                'a_attr' => [
                    'id' => 'menu-item-'.$node->getId(),
                    'class' => 'menu-dialog-link',
                    'href' => $this->admin->generateObjectUrl('edit', $node),
                    'data-root-id' => $node->getRoot()
                ],
                'data' => [
                    'id' => $node->getId(),
                    'rootId' => $node->getRoot(),
                    'externalUrl' => $node->getPage() ? $pageAdmin->generateObjectUrl('edit',$node->getPage()) : ($node->getRedirectUrl() ?: $node->getInternalUrl()),
                    'path' => $node->getPage() ? $node->getPage()->getPageName() : ($node->getRedirectUrl() ?: $node->getInternalUrl()),
                    'deleteUrl' => $this->admin->generateObjectUrl('delete', $node),
                ],
                'state' => [
                    'selected' => $selected === $node->getId(),
                    'opened' => $node->hasChild($selected),
                ],
            ];
            if ($node->getChildren()) {
                $children = $this->menuItemManager->getChildren($node, true);
                $item['children'] = $this->addMenuBranch($children, $selected, []);
            }

            $nodeArray[] = $item;
        }

        return $nodeArray;
    }

    /**
     * @param MenuItem[] $nodes
     * @param $selected
     * @param $nodeArray
     * @return mixed
     */
    private function addMenuPlacment($nodes, $selected, $nodeArray)
    {
        /** @var PageAdmin $pageAdmin */
        foreach ($nodes as $node) {
            $item = [
                'text' => $node->getName(),
                'data' => [
                    'id' => $node->getId(),
                    'rootId' => $node->getRoot(),
                ],
                'state' => [
                    'selected' => $selected === $node->getId(),
                    'opened' => true,
                ],
            ];
            if ($node->getChildren()) {
                $children = $this->menuItemManager->getChildren($node, true);
                $item['children'] = $this->addMenuBranch($children, $selected, []);
            }

            $nodeArray[] = $item;
        }

        return $nodeArray;
    }

    /**
     * {@inheritdoc}
     */
    public function createAction()
    {
        $this->caller = __FUNCTION__;
        /** @var Request $request */
        $request = $this->getRequest();

        if ($request->get('subclass') && $request->get('subclass') == 'menu') {
            if (false === $this->admin->isGranted('ROLE_SUPER_ADMIN')) {
                throw new AccessDeniedException();
            }
        }

        if ($request->get('subclass') == 'menu item') {
            $this->isNewMenuItem = true;
            if ($request->get('root_id')) {
                $this->get('session')->set('root_menu_id', $request->get('root_id'));
            }
        }

        $response = parent::createAction();
        if ($request->getMethod() === 'POST' && !$response instanceof JsonResponse) {
            $response->setStatusCode('402');
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function editAction($id = null)
    {
        $this->caller = __FUNCTION__;
        /** @var Request $request */
        $request = $this->getRequest();

        if ($request->get('subclass') && $request->get('subclass') == 'menu') {
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
        $this->caller = __FUNCTION__;
        /** @var Request $request */
        $request = $this->getRequest();

        $id = $request->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('DELETE', $object)) {
            throw new AccessDeniedException();
        }

        if ($request->get('subclass') && $request->get('subclass') == 'menu') {
            if (false === $this->admin->isGranted('ROLE_SUPER_ADMIN')) {
                throw new AccessDeniedException();
            }
        }

        if ($request->getMethod() == 'DELETE') {
            try {
                $this->currentMenuLanguage = $object->getLocale();
                $this->admin->delete($object);

                if ($this->isXmlHttpRequest()) {
                    return $this->renderJson(
                        [
                            'result' => 'ok',
                            'objectId' => $this->admin->getNormalizedIdentifier($object),
                            'status' => 'success',
                            'message' => $this->admin->trans('flash_delete_success', [], 'NetworkingInitCmsBundle'),
                        ]
                    );
                } else {
                    $this->addFlash(
                        'sonata_flash_success',
                        $this->trans('flash_delete_success', [], 'NetworkingInitCmsBundle')
                    );
                }
            } catch (ModelManagerException $e) {
                if ($this->isXmlHttpRequest()) {
                    return $this->renderJson(
                        [
                            'result' => 'ok',
                            'objectId' => $this->admin->getNormalizedIdentifier($object),
                            'status' => 'error',
                            'message' => $this->admin->trans($e->getMessage()),
                        ]
                    );
                } else {
                    $this->addFlash(
                        'sonata_flash_error',
                        $this->trans('flash_delete_error', [], 'NetworkingInitCmsBundle')
                    );
                }
            }

            return new RedirectResponse($this->admin->generateUrl('list'));
        }

        return $this->render(
            $this->admin->getTemplate('delete'),
            [
                'object' => $object,
                'action' => 'delete',
            ]
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param $rootId
     * @param $pageId
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     */
    public function createFromPageAction(Request $request, $rootId, $pageId)
    {
        $rootNode = $this->admin->getObject($rootId);

        if (!$rootNode) {
            throw new NotFoundHttpException(sprintf('unable to find the Menu with id : %s', $rootId));
        }

        $page = $this->pageManager->find($pageId);

        if (!$page) {
            throw new NotFoundHttpException(sprintf('unable to find the Page with id : %s', $pageId));
        }

        $menuItem = $this->admin->getNewInstance();

        $menuItem->setPage($page);
        $menuItem->setRoot($rootNode);
        $menuItem->setParent($rootNode);
        $menuItem->setName($page->getTitle());

        $em = $this->getDoctrine()->getManager();
        $em->persist($menuItem);
        $em->flush();

        $this->admin->createObjectSecurity($menuItem);

        return $this->redirect(
            $this->admin->generateUrl('list', ['page_id' => $page->getId(), 'menu_id' => $menuItem->getId()]).'#end'
        );
    }

    /**
     * @param $request
     *
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function ajaxControllerAction(Request $request)
    {
        $operation = $request->request->get('operation') ? $request->request->get('operation') : $request->query->get(
            'operation'
        );

        if (!$operation) {
            throw new NotFoundHttpException();
        }

        return new JsonResponse($this->$operation());
    }

    /**
     * @return array
     */
    public function updateNodes()
    {
        /** @var Request $request */
        $request = $this->getRequest();
        $nodes = $request->get('nodes') ? $request->get('nodes') : [];

        $lastEdited = $this->get('session')->get('MenuItem.last_edited');

        try {
            foreach ($nodes as $node) {
                /** @var $menuItem BaseMenuItem */
                $menuItem = $this->admin->getObject($node['item_id']);
                if ($node['parent_id']) {
                    $parent = $this->admin->getObject($node['parent_id']);
                    $menuItem->setParent($parent);
                } else {
                    $menuItem->setParent($menuItem->getParentByLevel(0));
                }

                $menuItem->setLft($node['left']);
                $menuItem->setRgt($node['right']);
                $menuItem->setLvl($node['depth']);
                $this->admin->update($menuItem);
            }

            $response = ['status' => 'ok', 'message' => $this->admin->trans('info.menu_sorted')];
        } catch (\Exception $e) {
            $response = ['status' => 'error', 'message' => $this->admin->trans('info.menu_sorted_error')];
        }

        $this->get('session')->set('MenuItem.last_edited', $lastEdited);

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function renderJson($data, $status = 200, $headers = [])
    {
        $response = parent::renderJson($data, $status, $headers);

        $data = json_decode($response->getContent(), true);


        if (!array_key_exists('message', $data)) {

            /** @var Request $request */
            $request = $this->getRequest();

            if ($data['result'] == 'ok') {
                $message = 'flash_'.str_replace('Action', '', $this->getCaller()).'_success';
                $data['is_new_menu_item'] = $this->isNewMenuItem;
                if ($this->isNewMenuItem) {
                    $data['html'] = $this->placementAction($request);
                }
                $data['status'] = 'success';
            } elseif ($data['result'] == 'error') {
                $data['status'] = 'error';
                $message = 'flash_'.str_replace('Action', '', $this->getCaller()).'_error';
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
     * renders the template html for the modal.
     *
     * @return bool|string|Response
     *
     * @throws \Sonata\AdminBundle\Exception\NoValueException
     */
    public function placementAction(Request $request)
    {
        /** @var \Networking\InitCmsBundle\Entity\MenuItem $rootNode */
        $rootNode = $this->admin->getObject($request->getSession()->get('root_menu_id'));
        $selected = $request->getSession()->get('MenuItem.last_edited');
        if (!$rootNode) {
            throw new NotFoundHttpException();
        }

        if ($rootNode->getChildren()->count() > 1) {
            $children = $this->menuItemManager->getChildren($rootNode, true);
            $params = [
                'admin' => $this->admin,
                'menu' => [
                    'rootNode' => $rootNode,
                    'navigation' => $this->addMenuPlacment($children, $selected, []),
                ],
            ];
            $template = '@NetworkingInitCms/MenuItemAdmin/placement.html.twig';

            return $this->renderView($template, $params);
        } else {
            return false;
        }
    }

    /**
     * @return string
     */
    public function getCaller()
    {
        return $this->caller ?: 'global';
    }


    /**
     * @param Request $request
     * @param $newMenuItemId
     * @param $menuItemId
     *
     * @return Response
     */
    public function newPlacementAction(Request $request, $newMenuItemId, $menuItemId)
    {
        $this->caller = 'createAction';

        $sibling = $request->get('sibling');


        $newMenuItem = $this->menuItemManager->find($newMenuItemId);
        $menuItem = $this->menuItemManager->find($menuItemId);
        if (!$newMenuItem || !$menuItem) {
            throw new NotFoundHttpException();
        }

        $data = [];
        try {
            if ($sibling) {
                $this->menuItemManager->persistAsNextSiblingOf($newMenuItem, $menuItem);
            } else {
                $this->menuItemManager->persistAsFirstChildOf($newMenuItem, $menuItem);
            }
            $this->getDoctrine()->getManager()->flush();
            $data = [
                'result' => 'ok',
                'objectId' => $this->admin->getNormalizedIdentifier($newMenuItem),
            ];

            return $this->renderJson($data);
        } catch (\Exception $e) {
            return $this->renderJson($data);
        }
    }
}
