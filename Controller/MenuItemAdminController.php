<?php

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
 *
 */
class MenuItemAdminController extends CRUDController
{

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listAction()
    {
        return $this->navigationAction();
    }

    /**
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws AccessDeniedException
     */
    public function createAction()
    {
        if ($this->admin->hasActiveSubClass()) {
            $subclass = $this->get('request')->get('subclass');
            if ($subclass == 'menu') {
                $this->admin->setIsRoot(true);
            }
        }

        return parent::createAction();
    }

    /**
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws NotFoundHttpException
     * @throws AccessDeniedException
     */
    public function editAction($id = null)
    {
        $id = $this->get('request')->get($this->admin->getIdParameter());

        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('EDIT', $object)) {
            throw new AccessDeniedException();
        }

        if($object->getId() === $object->getRoot()){
            $this->admin->setIsRoot(true);
        }

        return parent::editAction($id);
    }

    /**
     * @Template()
     */
    public function navigationAction()
    {

        $router = $this->get('router');
        $menus = array();
        /** @var $repository MenuItemRepository */
        $repository = $this->getDoctrine()
            ->getRepository('NetworkingInitCmsBundle:MenuItem');

        $rootNodes = $repository->getRootNodesByLocale($this->getRequest()->getLocale());

        $childOpen = function ($node) {
            return sprintf('<li id="listItem_%s">', $node['id']);
        };

        $nodeDecorator = function ($node) use ($router) {
            return sprintf(
                '<div><a href="%s">%s</a></div>',
                $router->generate('admin_networking_initcms_menuitem_edit', array('id' => $node['id'])),
                $node['name']
            );
        };

        foreach ($rootNodes as $rootNode) {
            $navigation = $repository->childrenHierarchy(
                $rootNode,
                null,
                array(
                    'decorate' => true,
                    'childOpen' => $childOpen,
                    'childClose' => '</li>',
                    'nodeDecorator' => $nodeDecorator
                ),
                true
            );
            $menus[] = $navigation;
        }

        return $this->render('NetworkingInitCmsBundle:MenuItemAdmin:navigation.html.twig', array('menus' => $menus, 'action' => 'navigation'));
    }

    /**
     * @param $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function ajaxNavigationAction(Request $request)
    {
        $operation = $request->request->get('operation') ? $request->request->get('operation') : $request->query->get('operation');

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
            $parent = null;
            /** @var $menuItem MenuItem */
            $menuItem = $repository->find($node['item_id']);
            if (!$menuItem->getPage()) continue;

            if ($node['parent_id']) {
                $parent = $repository->find($node['parent_id']);
            }

            $menuItem->setParent($parent);
            $menuItem->setLft($node['left']);
            $menuItem->setRgt($node['right']);
            $menuItem->setLvl($node['depth']);
            $em->persist($menuItem);
        }

        $em->flush();

        $response = array('status' => 1);

        return $response;
    }
}
