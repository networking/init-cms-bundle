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
    Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
    public function listAction()
    {
        if (false === $this->admin->isGranted('LIST')) {
            throw new AccessDeniedException();
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
        $formView = $datagrid->getForm()->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('form')->renderer->setTheme($formView, $this->admin->getFilterTheme());

        if ($this->isXmlHttpRequest()) {
            return $this->renderView(
                'NetworkingInitCmsBundle:MenuItemAdmin:menu_tabs.html.twig',
                array(
                    'menus' => $menus,
                    'admin' => $this->admin
                )
            );
        }

        return $this->render(
            $this->admin->getTemplate('list'),
            array(
                'action' => 'list',
                'form' => $formView,
                'datagrid' => $datagrid,
                'menus' => $menus,
                'action' => 'navigation'
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
                            'objectId' => $this->admin->getNormalizedIdentifier($object)
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
                            'objectId' => $this->admin->getNormalizedIdentifier($object)
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


        return $this->redirect($this->admin->generateUrl('list'));

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

        $response = array('status' => 1);

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

        $data['html'] = $this->listAction();

        if ($response instanceof JsonResponse) {
            $response->setData($data);
        } else {
            $response->setContent($data);
        }

        return $response;
    }
}
