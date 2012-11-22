<?php

namespace Networking\InitCmsBundle\Controller;

use Networking\InitCmsBundle\Entity\MenuItem;
use Networking\InitCmsBundle\Entity\MenuItemRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Sonata\AdminBundle\Controller\CRUDController;
use Symfony\Component\HttpFoundation\JsonResponse;

class MenuItemAdminController extends CRUDController
{
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

        $childOpen = function($node) {
            return sprintf('<li id="listItem_%s">', $node['id']);
        };

        $nodeDecorator = function($node) use ($router) {
            return sprintf(
                '<div><a href="%s">%s</a></div>',
                $router->generate('admin_networking_init_cms_menuitem_edit', array('id' => $node['id'])),
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
