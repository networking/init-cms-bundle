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

        $em = $this->getDoctrine()->getManager();
        /** @var $repo \Gedmo\Loggable\Entity\Repository\LogEntryRepository*/
        $repo = $this->getDoctrine()->getRepository('NetworkingInitCmsBundle:PageSnapshot');

        $article = $repo->findOneBy(array('pageId' => $id), array('id' => 'DESC'));


        $content = unserialize($article->getContent());
        var_dump($article->getContentRoute());
        foreach ($content as $layoutBlock){
            var_dump($layoutBlock);
        }
        die;

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('VIEW', $object)) {
            throw new AccessDeniedException();
        }

        $this->admin->setSubject($object);

        return $this->render($this->admin->getTemplate('show'), array(
            'action'   => 'show',
            'object'   => $object,
            'elements' => $this->admin->getShow(),
        ));
    }
}
