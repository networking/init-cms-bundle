<?php

declare(strict_types=1);

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
use Doctrine\Persistence\ManagerRegistry;
use Networking\InitCmsBundle\Admin\Entity\PageAdmin;
use Networking\InitCmsBundle\Component\EventDispatcher\CmsEventDispatcher;
use Networking\InitCmsBundle\Entity\BaseMenuItem;
use Networking\InitCmsBundle\Cache\PageCacheInterface;
use Networking\InitCmsBundle\Entity\MenuItem;
use Networking\InitCmsBundle\Model\MenuItemManagerInterface;
use Networking\InitCmsBundle\Model\PageManagerInterface;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Networking\InitCmsBundle\Entity\MenuItemManager;

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
    public function listAction(Request $request, $pageId = false, $menuId = false, $ajaxTemplate = 'menu_tabs', $rootMenuId = null): Response
    {
        $this->caller = __FUNCTION__;
        $this->admin->checkAccess('list');

        if ($request->get('page_id')) {
            $pageId = $request->get('page_id');
        }

        if ($request->get('menu_id')) {
            $menuId = $request->get('menu_id');
            if ($menuId) {
                $request->getSession()->set('MenuItem.last_edited', $menuId);

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

        $menus = [];

        /*
         * if the list was filtered or a new menu entry was posted,
         * use the submitted locale for list, else set the locale
         * to the users default within the availble CMS frontend languages
         */

        if ($menuId) {
            $menu = $this->menuItemManager->find($menuId);

            if ($menu) {
                $this->currentMenuLanguage = $menu->getLocale();
            }
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

        //use one of the locale to filter the list of menu entries (see above
        $rootNodes = $this->menuItemManager->getRootNodesByLocale($this->currentMenuLanguage, 'id');

        $childOpen = fn($node): string => sprintf('<li class="table-row-style" id="listItem_%s">', $node['id']);
        $admin = $this->admin;
        $controller = $this;
        /** @var ArrayCollection $menuAllItems */
        $menuAllItems = new ArrayCollection($this->menuItemManager->findAllJoinPage());


        $selected = $request->getSession()->get('MenuItem.last_edited');
        $rootNodes = $this->menuItemManager->getRootNodesByLocale($this->currentMenuLanguage, 'id');
        foreach ($rootNodes as $rootNode) {
            $children = $this->menuItemManager->getChildren($rootNode, true);
            $menus[] = [
                'rootNode' => $rootNode,
                'navigation' => $this->addMenuBranch($children, $selected, []),
            ];
        }

        $datagrid = $this->admin->getDatagrid();

        if ($menuId) {
            $menu = $this->menuItemManager->find($menuId);

            if ($menu) {
                $locale = $menu->getLocale();
                $datagrid->setValue('locale', null, $locale);
                $datagrid->buildPager();
            }
        }

        $formView = $datagrid->getForm()->createView();

        // set the theme for the current Admin Form
        $this->setFormTheme($formView, $this->admin->getFilterTheme());

        $lastEdited = $request->getSession()->get('MenuItem.last_edited');

        if ($this->isXmlHttpRequest($request)) {

            $parameters = [
                'menus' => $menus,
                'admin' => $this->admin,
                'page_id' => $pageId,
            ];

            $html = $this->renderView('@NetworkingInitCms/MenuItemAdmin/'.$ajaxTemplate.'.html.twig', $this->addRenderExtraParams($parameters));
            return $this->renderJson(['html' => $html, 'last_editied' => $lastEdited], 200, []);
        }



        return $this->renderWithExtraParams(
            $this->admin->getTemplateRegistry()->getTemplate('list'),
            [
                'action' => 'list',
                'form' => $formView,
                'datagrid' => $datagrid,
                'menus' => $menus,
                'last_edited' => $lastEdited,
                'page_id' => $pageId,
                'menu_id' => $menuId,
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function createAction(Request $request): Response
    {
        $this->caller = __FUNCTION__;

        if ($request->get('subclass') && $request->get('subclass') == 'menu') {
            if (false === $this->admin->isGranted('ROLE_SUPER_ADMIN')) {
                throw new AccessDeniedException();
            }
        }

        if ($request->query->get('subclass') == 'menu item') {
            $this->isNewMenuItem = true;
            if ($request->query->get('root_id')) {
                $request->getSession()->set('root_menu_id', $request->query->get('root_id'));
            }
        }


        $reponse =  parent::createAction($request);

        if($this->isXmlHttpRequest($request) && $request->isMethod('POST')){
            $reponse = $this->getJsonResponse($request, $reponse);
        }

        return $reponse;
    }

    /**
     * {@inheritdoc}
     */
    public function editAction(Request $request): Response
    {
        $this->caller = __FUNCTION__;
        if ($request->get('subclass') && $request->get('subclass') == 'menu') {
            if (false === $this->admin->isGranted('ROLE_SUPER_ADMIN')) {
                throw new AccessDeniedException();
            }
        }

        $reponse =  parent::editAction($request);

        if($this->isXmlHttpRequest($request) && $request->isMethod('POST')){
            $reponse = $this->getJsonResponse($request, $reponse);
        }

        return $reponse;
    }

    /**
     * {@inheritdoc}
     */
    public function deleteAction(Request $request): Response
    {
        $id = null;
        $object = $this->assertObjectExists($request, true);
        \assert(null !== $object);

        $this->admin->checkAccess('delete', $object);

        $this->caller = __FUNCTION__;


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

        if (\in_array($request->getMethod(), [Request::METHOD_POST, Request::METHOD_DELETE], true)) {
            try {
                $this->currentMenuLanguage = $object->getLocale();
                $this->admin->delete($object);

                if ($this->isXmlHttpRequest($request)) {
                    return $this->getJsonResponse($request,
                        [
                            'result' => 'ok',
                            'objectId' => $this->admin->getNormalizedIdentifier($object),
                            'status' => 'success',
                            'message' => $this->trans('flash_delete_success', [], 'NetworkingInitCmsBundle'),
                        ]
                    );
                } else {
                    $this->addFlash('sonata_flash_success', $this->trans('flash_delete_success', [], 'NetworkingInitCmsBundle'));
                }
            } catch (ModelManagerException $e) {
                if ($this->isXmlHttpRequest($request)) {
                    return $this->getJsonResponse($request,
                        [
                            'result' => 'ok',
                            'objectId' => $this->admin->getNormalizedIdentifier($object),
                            'status' => 'error',
                            'message' => $this->trans($e->getMessage(), [], $this->admin->getTranslationDomain()),
                        ]
                    );
                } else {
                    $this->addFlash('sonata_flash_error', $this->trans('flash_delete_error', [], 'NetworkingInitCmsBundle'));
                }
            }

            return new RedirectResponse($this->admin->generateUrl('list'));
        }

        return $this->renderWithExtraParams(
            $this->admin->getTemplateRegistry()->getTemplate('delete'),
            [
                'object' => $object,
                'action' => 'delete',
            ]
        );
    }

    /**
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

        $modelManager = $this->admin->getModelManager();
        $modelManager->create($menuItem);

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
        $operation = $request->request->get('operation') ?: $request->query->get(
            'operation'
        );

        if (!$operation) {
            throw new NotFoundHttpException();
        }

        return new JsonResponse($this->$operation($request));
    }

    /**
     * @return array
     */
    public function updateNodes(Request $request)
    {
        $nodes = $request->get('nodes') ?: [];

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

            $response = ['status' => 'ok', 'message' => $this->trans('info.menu_sorted')];
        } catch (\Exception) {
            $response = ['status' => 'error', 'message' => $this->trans('info.menu_sorted_error')];
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function getJsonResponse(Request $request, $data, $status = 200, $headers = [])
    {
        $message = null;
        if($data instanceof JsonResponse){
            $status = $data->getStatusCode();
            $data = json_decode($data->getContent(), true, 512, JSON_THROW_ON_ERROR);
        }


        if (!array_key_exists('message', $data)) {

            if ($status == 200) {

                $message = 'flash_'.str_replace('Action', '', $this->getCaller()).'_success';
                $data['is_new_menu_item'] = $this->isNewMenuItem;
                if ($this->isNewMenuItem) {

                    $data['html'] = $this->placementAction($request);
                }
                $data['status'] = 'success';

            }



            if ($status !== 200) {
                $data['status'] = 'error';
                $message = 'flash_'.str_replace('Action', '', $this->getCaller()).'_error';

                $data['errors'] = [];
                foreach ($data['violations'] as $violation) {
                    $data['errors'][] = $violation['title'];
                }
            }

            if ($message) {
                $data['message'] = $this->trans($message);
            }
        }

        if (!array_key_exists('html', $data)) {
            $response = $this->listAction($request, $request->getSession()->get('admin/last_page_id'));
            $data['html'] = $response->getContent();
        }

        return new JsonResponse($data, $status, $headers);
    }

    /**
     * renders the template html for the modal.
     *
     *
     * @throws \Sonata\AdminBundle\Exception\NoValueException
     */
    public function placementAction(Request $request): bool|string|\Symfony\Component\HttpFoundation\Response
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

    private function addMenuPlacment($nodes, $selected, $nodeArray): array
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
     * @return string
     */
    public function getCaller()
    {
        return $this->caller ?:'global';
    }

    /**
     * @param $rootNode
     * @param $admin
     * @param $controller
     *
     * @return mixed
     */
    public function createPlacementNavigation(Request $request, $rootNode, $admin, $controller)
    {


        $lastEdited = $request->getSession()->get('MenuItem.last_edited');

        $menuItemManager = $this->menuItemManager;
        $nodeDecorator = function ($node) use ($admin, $controller, $menuItemManager, $lastEdited) {
            if ($lastEdited == $node['id']) {
                return;
            }
            $node = $menuItemManager->find($node['id']);

            return $controller->renderView(
                '@NetworkingInitCms/MenuItemAdmin/placement_item.html.twig',
                ['admin' => $admin, 'last_edited' => $lastEdited, 'node' => $node]
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
                '@NetworkingInitCms/MenuItemAdmin/placement_item_end.html.twig',
                ['admin' => $admin, 'last_edited' => $lastEdited, 'node' => $node]
            );
        };

        $rootOpen = function ($tree): string {
            $node = $tree[0];
            if ($node['lvl'] == 1) {
                $class = 'ui-sortable';
            } else {
                $class = 'table-row-style';
            }

            return sprintf('<ul class="%s">', $class);
        };

        $navigation = $this->menuItemManager->childrenHierarchy(
            $rootNode,
            null,
            [
                'rootOpen' => $rootOpen,
                'decorate' => true,
                'childOpen' => $childOpen,
                'childClose' => $childClose,
                'nodeDecorator' => $nodeDecorator,
            ],
            false
        );

        return $navigation;
    }

    /**
     * @param $newMenuItemId
     * @param $menuItemId
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
            $this->container->get('doctrine')->getManager()->flush();
            $data = [
                'result' => 'ok',
                'objectId' => $this->admin->getNormalizedIdentifier($newMenuItem),
            ];
            return $this->getJsonResponse($request, $data);
        } catch (\Exception) {
            return $this->getJsonResponse($request, $data);
        }
    }

    public static function getSubscribedServices(): array
    {
        return [
            'doctrine' => '?'.ManagerRegistry::class,
            ] + parent::getSubscribedServices(
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
}
