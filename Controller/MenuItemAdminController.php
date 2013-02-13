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

use Networking\InitCmsBundle\Entity\MenuItem,
    Networking\InitCmsBundle\Entity\MenuItemRepository,
    Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    Sonata\AdminBundle\Controller\CRUDController,
    Symfony\Component\HttpFoundation\JsonResponse,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpKernel\Exception\NotFoundHttpException,
    Symfony\Component\Security\Core\Exception\AccessDeniedException,
    Networking\UserBundle\Entity\AdminSettings;

/**
 * @author net working AG <info@networking.ch>
 */
class MenuItemAdminController extends CmsCRUDController
{

    protected $currentMenuLanguage = '';

    /**
     * return the Response object associated to the list action
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     *
     * @return Response
     */
    public function listAction($pageId = false, $menuId = false)
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

        /** @var $repository MenuItemRepository */
        $repository = $this->getDoctrine()
            ->getRepository('NetworkingInitCmsBundle:MenuItem');

        /*
         * if the list was filtered or a new menu entry was posted,
         * use the submitted locale for list, else set the locale
         * to the users default within the availble CMS frontend languages
         */

        if ($menuId) {
            $menu = $this->getDoctrine()->getRepository('NetworkingInitCmsBundle:Menuitem')->find($menuId);

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
        $rootNodes = $repository->getRootNodesByLocale($this->currentMenuLanguage);

        $childOpen = function ($node) {
            return sprintf('<li class="table-row-style" id="listItem_%s">', $node['id']);
        };
        $admin = $this->admin;
        $controller = $this;
        $nodeDecorator = function ($node) use ($admin, $controller, $repository) {
            $node = $repository->find($node['id']);

            return $controller->renderView(
                'NetworkingInitCmsBundle:MenuItemAdmin:menu_list_item.html.twig',
                array('admin' => $admin, 'node' => $node)
            );
        };

        foreach ($rootNodes as $rootNode) {
            $navigation = $repository->childrenHierarchy(
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


        $datagrid = $this->admin->getDatagrid();

        if ($menuId) {
            $menu = $this->getDoctrine()->getRepository('NetworkingInitCmsBundle:Menuitem')->find($menuId);

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
            return $this->renderView(
                'NetworkingInitCmsBundle:MenuItemAdmin:menu_tabs.html.twig',
                array(
                    'menus' => $menus,
                    'admin' => $this->admin,
                    'page_id' => $pageId
                )
            );
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
                'page_id' => $pageId
            )
        );
    }


    /**
     * @param mixed $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response|void
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
                    $this->get('session')->setFlash('sonata_flash_success', 'flash_delete_success');
                }
            } catch (ModelManagerException $e) {
                if ($this->isXmlHttpRequest()) {
                    return $this->renderJson(
                        array(
                            'result' => 'error',
                            'objectId' => $this->admin->getNormalizedIdentifier($object),
                            'status' => 'success',
                            'message' => $this->admin->trans('flash_delete_error')
                        )
                    );
                } else {
                    $this->get('session')->setFlash('sonata_flash_error', 'flash_delete_error');
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

        $page = $this->getDoctrine()->getRepository('NetworkingInitCmsBundle:Page')->find($pageId);

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


        return $this->redirect($this->admin->generateUrl('list', array('page_id' => $page->getId(), 'menu_id' => $menuItem->getId())));

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
        $nodes = $request->query->get('nodes') ? $request->query->get('nodes') : array();

        $repository = $this->getDoctrine()
            ->getRepository('NetworkingInitCmsBundle:MenuItem');

        $em = $this->get('doctrine.orm.entity_manager');
        try {
            foreach ($nodes as $node) {
                /** @var $menuItem MenuItem */
                $menuItem = $repository->find($node['item_id']);
                if (!$menuItem->getPage()) {
                    continue;
                }

                if ($node['parent_id']) {
                    $parent = $repository->find($node['parent_id']);
                    $menuItem->setParent($parent);
                } else {
                    $parent = $repository->find($menuItem->getRoot());
                    $menuItem->setParent($parent);
                }


                $menuItem->setLft($node['left']);
                $menuItem->setRgt($node['right']);
                $menuItem->setLvl($node['depth']);
                $em->persist($menuItem);
            }

            $em->flush();

            $response = array('status' => 'ok', 'message' => $this->admin->trans('info.menu_sorted'));
        } catch (\Exception $e) {
            $response = array('status' => 'error', 'message' => $this->admin->trans('info.menu_sorted_error'));
        }

        return $response;
    }

    /**
     * @param mixed $data
     * @param int $status
     * @param array $headers
     * @return \Symfony\Component\HttpFoundation\JsonResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function renderJson($data, $status = 200, $headers = array())
    {

        $response = parent::renderJson($data, $status, $headers);

        $data = json_decode($response->getContent(), true);

        $data['html'] = $this->listAction($this->get('session')->get('admin/last_page_id'));

        if (!array_key_exists('message', $data)) {
            if ($message = $this->get('session')->getFlash('sonata_flash_success')) {
                $data['status'] = 'success';
            } elseif ($message = $this->get('session')->getFlash('sonata_flash_error')) {
                $data['status'] = 'error';
            }
            if ($message) {
                $data['message'] = $this->admin->trans($message);
            }
        }

        if ($response instanceof JsonResponse) {
            $response->setData($data);
        } else {
            $response->setContent($data);
        }

        return $response;
    }
}
