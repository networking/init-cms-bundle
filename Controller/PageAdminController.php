<?php

namespace Networking\InitCmsBundle\Controller;

use Networking\InitCmsBundle\Entity\Page;
use Networking\InitCmsBundle\Entity\LayoutBlock;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Admin\Admin as SontataAdmin;

class PageAdminController extends CRUDController
{

    /**
     * @Template()
     */
    public function translatePageAction(Request $request, $id, $locale)
    {
        $repository = $this->getDoctrine()->getRepository('NetworkingInitCmsBundle:Page');

        /** @var $page Page */
        $page = $repository->find($id);

        $em = $this->get('doctrine.orm.entity_manager');

        $pageCopy = new Page();

        $pageCopy->setTitle($page->getTitle());
        $pageCopy->setUrl($page->getUrl());
        $pageCopy->setMetaKeyword($page->getMetaKeyword());
        $pageCopy->setMetaDescription($page->getMetaDescription());
        $pageCopy->setNavigationTitle($page->getNavigationTitle());
        $pageCopy->setActiveFrom($page->getActiveFrom());
        $pageCopy->setActiveTill($page->getActiveTill());
        $pageCopy->setShowInNavigation($page->getShowInNavigation());
        $pageCopy->setIsHome($page->getIsHome());
        $pageCopy->setLocale($locale);
        $pageCopy->setTemplate($page->getTemplate());
        $pageCopy->setOriginal($page);

        $em->persist($pageCopy);

        $em->flush();
        $this->get('session')->setFlash('sonata_flash_success', 'The page was successfully translated');

        /** @var $admin \Networking\InitCmsBundle\Admin\PageAdmin */
        $admin = $this->container->get('networking_init_cms.page.admin.page');

        return $this->redirect($admin->generateUrl('edit', array('id' => $id)));
    }

    /**
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @param Symfony\Component\HttpFoundation\Request $request
     *
     * @return Symfony\Component\HttpFoundation\Response
     */
    public function updateFormFieldElementAction(Request $request)
    {
        $twig = $this->get('twig');
        $helper = $this->get('sonata.admin.helper');

        $code = $request->get('code');
        $elementId = $request->get('elementId');
        $objectId = $request->get('objectId');
        $uniqid = $request->get('uniqid');

        /** @var $admin SontataAdmin */
        $admin = $this->container->get($code);
        $admin->setRequest($request);

        if ($uniqid) {
            $admin->setUniqid($uniqid);
        }

        $subject = $admin->getModelManager()->find($admin->getClass(), $objectId);
        if ($objectId && !$subject) {
            throw new NotFoundHttpException;
        }

        if (!$subject) {
            $subject = $admin->getNewInstance();
        }

        $admin->setSubject($subject);
        $formBuilder = $admin->getFormBuilder();

        $form = $formBuilder->getForm();
        $form->setData($subject);
        $form->bind($admin->getRequest());

        // create a fresh form taking into consideration deleted fields and layoutblock position
        $finalForm = $admin->getFormBuilder()->getForm();
        $finalForm->setData($subject);

        // bind the data
        $finalForm->setData($form->getData());

        $view = $helper->getChildFormView($finalForm->createView(), $elementId);

        $extension = $twig->getExtension('form');
        $extension->initRuntime($twig);
        $extension->renderer->setTheme($view, $admin->getFormTheme());

        return new Response($extension->renderer->searchAndRenderBlock($view, 'widget'));
    }

    /**
     * @param  Symfony\Component\HttpFoundation\Request   $request
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws AccessDeniedException
     */
    public function addLayoutBlockAction(Request $request)
    {
        $twig = $this->get('twig');
        $helper = $this->get('networking_init_cms.admin.helper');
        $code = $request->get('code');
        $elementId = $request->get('elementId');
        $objectId = $request->get('objectId');
        $uniqid = $request->get('uniqid');

        $admin = $this->container->get($code);
        $admin->setRequest($request);

        if ($uniqid) {
            $admin->setUniqid($uniqid);
        }

        $subject = $admin->getModelManager()->find($admin->getClass(), $objectId);
        if ($objectId && !$subject) {
            throw new NotFoundHttpException;
        }

        if (!$subject) {
            $subject = $admin->getNewInstance();
        }

        $admin->setSubject($subject);

        $data = array(
            'zone' => $request->get('zone'),
            'sortOrder' => $request->get('sortOrder'),
            'page' => $subject,
            'classType' => $request->get('classType')
        );

        $helper->setNewLayoutBlockParameters($data);
        list($fieldDescription, $form) = $helper->appendFormFieldElement($admin, $subject, $elementId);

        /** @var $form \Symfony\Component\Form\Form */
        $view = $helper->getChildFormView($form->createView(), $elementId);

        // render the widget
        // todo : fix this, the twig environment variable is not set inside the extension ...

        $extension = $twig->getExtension('form');
        $extension->initRuntime($twig);
        $extension->renderer->setTheme($view, $admin->getFormTheme());

        return new Response($extension->renderer->searchAndRenderBlock($view, 'widget'));
    }
}
