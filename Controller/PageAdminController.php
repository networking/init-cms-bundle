<?php

namespace Networking\InitCmsBundle\Controller;

use Networking\InitCmsBundle\Entity\Page;
use Networking\InitCmsBundle\Entity\LayoutBlock;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;

use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Controller\CRUDController;
use Sonata\AdminBundle\Admin\Admin as SontataAdmin;

/**
 *
 */
class PageAdminController extends CmsCRUDController
{

    /**
     * @Template()
     */
    public function translatePageAction(Request $request, $id, $locale)
    {
        $er = $this->getDoctrine()->getRepository('NetworkingInitCmsBundle:Page');

        /** @var $page Page */
        $page = $er->find($id);

        $em = $this->get('doctrine.orm.entity_manager');

        $pageCopy = new Page();

        $pageCopy->setTitle($page->getTitle());
        $pageCopy->setUrl($page->getUrl());
        $pageCopy->setMetaKeyword($page->getMetaKeyword());
        $pageCopy->setMetaDescription($page->getMetaDescription());
//        $pageCopy->setNavigationTitle($page->getNavigationTitle());
        $pageCopy->setActiveFrom($page->getActiveFrom());
//        $pageCopy->setActiveTill($page->getActiveTill());
//        $pageCopy->setShowInNavigation($page->getShowInNavigation());
        $pageCopy->setIsHome($page->getIsHome());
        $pageCopy->setLocale($locale);
        $pageCopy->setTemplate($page->getTemplate());
        $pageCopy->setOriginal($page);
        $em->persist($pageCopy);
        $em->flush();

        $this->get('session')->setFlash('sonata_flash_success', $this->translate('message.translation_saved'));

        /** @var $admin \Networking\InitCmsBundle\Admin\PageAdmin */
        $admin = $this->container->get('networking_init_cms.page.admin.page');

        return $this->redirect($admin->generateUrl('edit', array('id' => $id)));
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param $id
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function linkAction(Request $request, $id, $locale)
    {
        $er = $this->getDoctrine()->getRepository('NetworkingInitCmsBundle:Page');

        /** @var $page Page */
        $page = $er->find($id);

        if (!$page) {
            throw new NotFoundHttpException(sprintf('unable to find the Page with id : %s', $id));
        }

        if ($this->getRequest()->getMethod() == 'POST') {

              $linkPageId = $this->getRequest()->get('page');
              if(!$linkPageId){
                  $this->get('session')->setFlash('sonata_flash_error', 'flash_link_error');
              } else {
                  /** @var $linkPage Page */
                  $linkPage = $er->find($linkPageId);

                  $page->addTranslation($linkPage);

                  $em = $this->getDoctrine()->getManager();
                  $em->persist($page);
                  $em->flush();

                  if($this->isXmlHttpRequest()){

                      $html = $this->renderView(
                          'NetworkingInitCmsBundle:CRUD:page_translation_settings.html.twig',
                          array('object' => $page, 'admin' => $this->admin));
                      return $this->renderJson(array(
                                             'result' => 'ok',
                                             'html' => $html
                                         ));
                  }


                  $this->get('session')->setFlash('sonata_flash_success', 'flash_link_success');

                  return new RedirectResponse($this->admin->generateUrl('edit', array('id' => $page->getId())));
              }
        }

        $pages = $er->findBy(array('locale' => $locale));

        if (count($pages)) {
            $pages = new \Doctrine\Common\Collections\ArrayCollection($pages);
            $originalLocale = $page->getLocale();
            $pages = $pages->filter(function (Page $linkPage) use($originalLocale) {
                return !in_array($originalLocale, $linkPage->getTranslatedLocales());

            });
        }

        return $this->render(
            'NetworkingInitCmsBundle:CRUD:page_translation_link_list.html.twig',
            array(
                'page' => $page,
                'pages' => $pages,
                'locale' => $locale,
                'original_language' => \Locale::getDisplayLanguage($page->getLocale()),
                'language' => \Locale::getDisplayLanguage($locale),
                'admin' => $this->admin
            )
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param $id
     * @param $translationId
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function unlinkAction(Request $request, $id, $translationId)
    {
        $er = $this->getDoctrine()->getRepository('NetworkingInitCmsBundle:Page');

        /** @var $page Page */
        $page = $er->find($id);

        if (!$page) {
            throw new NotFoundHttpException(sprintf('unable to find the Page with id : %s', $id));
        }

        $locale = $page->getLocale();

        $qb = $er->createQueryBuilder('p');
        $qb->where('locale != :locale');


    }

    /**
     *
     * @param \Networking\InitCmsBundle\Controller\Symfony\Component\HttpFoundation\Request|\Symfony\Component\HttpFoundation\Request $request
     *
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
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
     * @param \Networking\InitCmsBundle\Controller\Symfony\Component\HttpFoundation\Request|\Symfony\Component\HttpFoundation\Request $request
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     * @return \Symfony\Component\HttpFoundation\Response
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

    /**
     * Handle uploads from the redactor editor
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     * @todo need to make a proper upload script
     */
    public function uploadTextBlockImageAction(Request $request)
    {
        $dir = $this->get('request')->getBasePath() . 'uploads/images/';


        $_FILES['file']['type'] = strtolower($_FILES['file']['type']);

        if ($_FILES['file']['type'] == 'image/png'
            || $_FILES['file']['type'] == 'image/jpg'
            || $_FILES['file']['type'] == 'image/gif'
            || $_FILES['file']['type'] == 'image/jpeg'
            || $_FILES['file']['type'] == 'image/pjpeg'
        ) {
            // setting file's mysterious name
            $filename = md5(date('YmdHis')) . '.jpg';

            if (!file_exists($dir)) {
                mkdir($dir, 0777, true);
            }

            $file = $dir . $filename;

            // copying
            copy($_FILES['file']['tmp_name'], $file);

            // displaying file
            $array = array(
                'filelink' => '/uploads/images/' . $filename
            );

            return new JsonResponse($array);

        }
    }

    /**
     * @param ProxyQueryInterface $selectedModelQuery
     * @return RedirectResponse
     * @throws AccessDeniedException
     */
    public function batchActionPublish(ProxyQueryInterface $selectedModelQuery)
    {
        if ($this->admin->isGranted('EDIT') === false || $this->admin->isGranted('DELETE') === false) {
            throw new AccessDeniedException();
        }

        $modelManager = $this->admin->getModelManager();

        $selectedModels = $selectedModelQuery->execute();


        // do the merge work here

        try {
            foreach ($selectedModels as $selectedModel) {
                $selectedModel->setStatus(Page::STATUS_PUBLISHED);
            }

            $modelManager->update($selectedModel);
        } catch (\Exception $e) {
            $this->get('session')->setFlash('sonata_flash_error', 'flash_batch_publish_error');

            return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
        }

        $this->get('session')->setFlash('sonata_flash_success', 'flash_batch_publish_success');

        return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function updateLayoutBlockSortAction(Request $request)
    {
        $layoutBlocks = $request->get('layoutBlocks');
        $zone = $request->get('zone');

        if ($layoutBlocks && is_array($layoutBlocks)) {
            foreach ($layoutBlocks as $key => $layoutBlockStr) {
                $sort = ++$key;
                $blockId = str_replace('layoutBlock_', '', $layoutBlockStr);
                $repo = $this->getDoctrine()->getRepository('NetworkingInitCmsBundle:LayoutBlock');

                try {
                    $layoutBlock = $repo->find($blockId);
                } catch (\Exception $e) {
                    $message = $e->getMessage();

                    return new JsonResponse(array('status' => 'error', 'message' => $message));
                }

                $layoutBlock->setSortOrder($sort);
                $layoutBlock->setZone($zone);

                $em = $this->getDoctrine()->getManager();
                $em->persist($layoutBlock);
            }

            $em->flush();
        }

        return new JsonResponse(
            array(
                'status' => 'success',
                'message' => $this->translate('message.layout_blocks_sorted', array('zone' => $zone))
            )
        );
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteLayoutBlockAction(Request $request)
    {
        $layoutBlockStr = $request->get('layoutBlock');

        if ($layoutBlockStr) {
            $blockId = str_replace('layoutBlock_', '', $layoutBlockStr);
            $repo = $this->getDoctrine()->getRepository('NetworkingInitCmsBundle:LayoutBlock');

            try {
                $layoutBlock = $repo->find($blockId);
            } catch (\Exception $e) {
                $message = $e->getMessage();

                return new JsonResponse(array('status' => 'error', 'message' => $message));
            }

            $em = $this->getDoctrine()->getManager();
            $em->persist($layoutBlock);
            $em->remove($layoutBlock);
            $em->flush();
        }
        return new JsonResponse(array('status' => 'success', 'message' => $this->translate('message.layout_block_deleted')));
    }

    /**
     * @param $string
     * @param array $params
     * @param null $domain
     * @return mixed
     */
    public function translate($string, $params = array(), $domain = null)
    {
        $translationDomain = $domain ? $domain : $this->admin->getTranslationDomain();

        return $this->get('translator')->trans($string, $params, $translationDomain);
    }

    /**
     * return the Response object associated to the view action
     *
     * @param null $id
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response
     */
    public function showAction($id = null)
    {
        $id = $this->get('request')->get($this->admin->getIdParameter());

        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('VIEW', $object)) {
            throw new AccessDeniedException();
        }

        $this->admin->setSubject($object);

        return $this->render($this->admin->getTemplate('show'), array(
            'action' => 'show',
            'object' => $object,
            'elements' => $this->admin->getShow(),
        ));
    }

    /**
     * return the Response object associated to the edit action
     *
     *
     * @param mixed $id
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     *
     * @return Response
     */
    public function editAction($id = null)
    {
        // the key used to lookup the template
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

        /** @var $form \Symfony\Component\Form\Form */
        $form = $this->admin->getForm();
        $form->setData($object);

        if ($this->get('request')->getMethod() == 'POST') {
            $form->bind($this->get('request'));

            $isFormValid = $form->isValid();

            // persist if the form was valid and if in preview mode the preview was approved
            if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {
                $this->admin->update($object);

                if ($this->isXmlHttpRequest()) {

                    $view = $form->createView();

                    // set the theme for the current Admin Form
                    $this->get('twig')->getExtension('form')->renderer->setTheme($view, $this->admin->getFormTheme());

                    $pageSettingsTemplate = $this->render($this->admin->getTemplate($templateKey), array(
                        'action' => 'edit',
                        'form' => $view,
                        'object' => $object,
                    ));

                    return $this->renderJson(array(
                        'result' => 'ok',
                        'objectId' => $this->admin->getNormalizedIdentifier($object),
                        'title' => $object->__toString(),
                        'status' => $this->admin->trans($object->getStatus()),
                        'pageSettings' => $pageSettingsTemplate
                    ));
                }

                $this->get('session')->setFlash('sonata_flash_success', 'flash_edit_success');
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

        $menuEr = $this->getDoctrine()->getRepository('NetworkingInitCmsBundle:MenuItem');

        $rootMenus = $menuEr->findBy(array('isRoot' => 1, 'locale' => $object->getLocale()));

        return $this->render($this->admin->getTemplate($templateKey), array(
            'action' => 'edit',
            'form' => $view,
            'object' => $object,
            'rootMenus' => $rootMenus
        ));
    }
}
