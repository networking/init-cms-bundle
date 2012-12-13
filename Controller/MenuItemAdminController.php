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

	public function listAction()
	{
		return $this->navigationAction();
	}

	public function editMenuAction($id)
	{

		$templateKey = 'edit';

		$id = $this->get('request')->get($this->admin->getIdParameter());

		$object = $this->admin->getObject($id);

		if (!$object) {
			throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
		}

		if (false === $this->admin->isGranted('EDIT', $object)) {
			throw new AccessDeniedException();
		}

		$this->admin->setSubject($object);
		$this->admin->setIsMenu(true);

		/** @var $form \Symfony\Component\Form\Form */
		$form = $this->admin->getForm();
		$form->setData($object);

		if ($this->get('request')->getMethod() == 'POST') {
			$form->bind($this->get('request'));

			$isFormValid = $form->isValid();

			// persist if the form was valid and if in preview mode the preview was approved
			if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {
				$this->admin->update($object);
				$this->get('session')->setFlash('sonata_flash_success', 'flash_edit_success');

				if ($this->isXmlHttpRequest()) {
					return $this->renderJson(array(
						'result' => 'ok',
						'objectId' => $this->admin->getNormalizedIdentifier($object)
					));
				}

				// redirect to edit mode
				return $this->redirectTo($object);
			}

			// show an error message if the form failed validation
			if (!$isFormValid) {
				$this->get('session')->setFlash('sonata_flash_error', 'flash_edit_error');
			} elseif ($this->isPreviewRequested()) {
				// enable the preview template if the form was valid and preview was requested
				$templateKey = 'preview';
			}
		}

		$view = $form->createView();

		// set the theme for the current Admin Form
		$this->get('twig')->getExtension('form')->renderer->setTheme($view, $this->admin->getFormTheme());

		return $this->render($this->admin->getTemplate($templateKey), array(
			'action' => 'edit',
			'form' => $view,
			'object' => $object,
		));
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
