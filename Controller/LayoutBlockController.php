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

use Networking\InitCmsBundle\Admin\Model\PageAdmin;
use Networking\InitCmsBundle\Model\PageInterface;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;

/**
 * Class PageAdminController
 * @package Networking\InitCmsBundle\Controller
 *
 * @author net working AG <info@networking.ch>
 */
class LayoutBlockController extends CRUDController
{
    protected $error = false;

    /**
     * @return Response
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function createAction()
    {
        if (!$this->isXmlHttpRequest()) {
            return new Response('cannot load external of page module', 500);
        }


        if (false === $this->admin->isGranted('CREATE')) {
            throw new AccessDeniedException();
        }

        $layoutBlock = $this->admin->getNewInstance();
        $request = $this->getRequest();
        $this->admin->setSubject($layoutBlock);

        $elementId = $request->get('elementId');
        $code = $request->get('code');
        $objectId = $request->get('objectId');
        $uniqid = $request->get('uniqid');
        $classType = $request->get('classType');
        /** @var \Sonata\AdminBundle\Admin\Admin Interface $pageAdmin */
        $pageAdmin = $this->container->get($code);
        $pageAdmin->setRequest($request);

        if ($uniqid) {
            $pageAdmin->setUniqid($uniqid);
        }

        $page = $this->get('networking_init_cms.page_manager')->find($objectId);
        if ($objectId && !$page) {
            throw new NotFoundHttpException;
        }

        if (!$page) {
            throw new NotFoundHttpException;
        }

        $this->getRequest()->attributes->add(array('objectId' => $objectId));
        $this->getRequest()->attributes->add(array('page_locale' => $page->getLocale()));

        $pageAdmin->setSubject($page);

        $layoutBlock->setZone($request->get('zone'));
        $layoutBlock->setSortOrder($request->get('sortOrder'));
        $layoutBlock->setClassType($classType);
        $layoutBlock->setPage($page);

        /** @var $form \Symfony\Component\Form\Form */
        $form = $this->admin->getForm();
        $form->setData($layoutBlock);

        if ($this->getRestMethod() == 'POST') {
            $form->submit($this->get('request'));

            $isFormValid = $form->isValid();
            try{
                // persist if the form was valid and if in preview mode the preview was approved
                if ($isFormValid && (!$this->isInPreviewMode() || $this->isPreviewApproved())) {
                    $this->admin->create($layoutBlock);

                    if ($this->isXmlHttpRequest()) {
                        return $this->renderJson(
                            array(
                                'result' => 'ok',
                                'status' => 'success',
                                'message' => $this->translate('message.layout_block_created'),
                                'layoutBlockId' => $this->admin->getNormalizedIdentifier($layoutBlock),
                                'zone' => $layoutBlock->getZone(),
                                'sortOder' => $layoutBlock->getSortOrder(),
                                'html' => $this->getLayoutBlockFormWidget($objectId, $elementId, $uniqid)
                            )
                        );
                    }
                }
            }catch (ModelManagerException $e){
                $formError = new FormError($e->getMessage());
                $form->addError($formError);
            }
        }

        $view = $form->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('form')->renderer->setTheme($view, $this->admin->getFormTheme());

        return $this->render(
            'NetworkingInitCmsBundle:PageAdmin:layout_block_edit.html.twig',
            array(
                'action' => 'create',
                'form' => $view,
                'object' => $layoutBlock,
                'code' => $code,
                'classType' => $request->get('classType'),
                'objectId' => $objectId,
                'uniqid' => $uniqid,
                'elementId' => $elementId
            )
        );
    }

    /**
     * @param Request $request
     * @return Response
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function updateFormFieldElementAction(Request $request)
    {

        $objectId = $request->get('objectId');
        $elementId = $request->get('elementId');
        $uniqId = $request->get('uniqid');
        $code = $request->get('code');

        $post = $request->request->all();

        if(empty($post)){
            $html = $this->getLayoutBlockFormWidget($objectId, $elementId, $uniqId, $code);
            return new Response($html, 200);
        }

        $post = $request->request->all();
        $post['page'] = $objectId;
        $post['content'] = $this->cleanContentString($post['content']);

        $layoutBlock = $this->admin->getObject($post['id']);

        /** @var $form \Symfony\Component\Form\Form */
        $form = $this->admin->getForm();
        $form->setData($layoutBlock);
        /** @var \Symfony\Component\Form\Extension\Csrf\CsrfProvider\SessionCsrfProvider  $csrf */
        $csrf = $this->get('form.csrf_provider');
        $token = $csrf->generateCsrfToken($form->getName());
        $post['_token'] = $token;
        unset($post['id']);

        $request->request->set($this->admin->getUniqid(), $post);

        $form->handleRequest($request);

        if($form->isValid()){
            $this->admin->update($layoutBlock);
            $html = $this->getLayoutBlockFormWidget($objectId, $elementId, $uniqId, $code);
            $status = 200;
            return new Response($html, $status);
        }else{
            $this->error = true;

            $response = new Response();
            $response->setStatusCode(500);
        }

        $view = $form->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('form')->renderer->setTheme($view, $this->admin->getFormTheme());

        return $this->render(
            'NetworkingInitCmsBundle:PageAdmin:layout_block_fields.html.twig',
            array(
                'form' => $view,
                'object' => $layoutBlock,
                'code' => $code,
                'classType' => $request->get('classType'),
                'objectId' => $objectId,
                'uniqid' => $uniqId,
                'elementId' => $elementId
            ),
            $response
        );
    }

    /**
     * @param $objectId
     * @param $elementId
     * @param null $uniqId
     * @param string $code
     * @return mixed
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    protected function getLayoutBlockFormWidget(
        $objectId,
        $elementId,
        $uniqId = null,
        $code = 'networking_init_cms.admin.page'
    ) {

        /** @var \Networking\InitCmsBundle\Admin\Model\PageAdmin $pageAdmin */
        $pageAdmin = $this->container->get($code);
        $pageAdmin->setRequest($this->getRequest());


        if ($uniqId) {
            $pageAdmin->setUniqid($uniqId);
        }

        $page = $pageAdmin->getModelManager()->find($pageAdmin->getClass(), $objectId);
        if ($objectId && !$page) {
            throw new NotFoundHttpException;
        }

        if (!$page) {
            $page = $pageAdmin->getNewInstance();
        }

        $this->getRequest()->attributes->add(array('objectId' => $objectId));
        $this->getRequest()->attributes->add(array('page_locale' => $page->getLocale()));

        $pageAdmin->setSubject($page);
        $formBuilder = $pageAdmin->getFormBuilder();

        /** @var \Symfony\Component\Form\Form $form */
        $form = $formBuilder->getForm();
        $form->setData($page);


        /** @var \Sonata\AdminBundle\Admin\AdminHelper $helper */
        $helper = $this->get('sonata.admin.helper');
        $view = $helper->getChildFormView($form->createView(), $elementId);

        $twig = $this->get('twig');
        $extension = $twig->getExtension('form');
        $extension->initRuntime($twig);
        $extension->renderer->setTheme($view, $this->admin->getFormTheme());

        return $extension->renderer->searchAndRenderBlock($view, 'widget');
    }

    /**
     * @param Request $request
     * @return JsonResponse
     */
    public function updateLayoutBlockSortAction(Request $request)
    {

        $zones = $request->get('zones', array());
        $objectId = $request->get('objectId');
        $pageAdmin = $this->container->get('networking_init_cms.admin.page');
        foreach ($zones as $zone) {
            $zoneName = $zone['zone'];
            if (array_key_exists('layoutBlocks', $zone) && is_array($zone['layoutBlocks'])) {
                foreach ($zone['layoutBlocks'] as $key => $layoutBlockStr) {
                    $sort = ++$key;
                    $blockId = str_replace('layoutBlock_', '', $layoutBlockStr);

                    if ($blockId) {
                        try {
                            /** @var \Networking\InitCmsBundle\Model\LayoutBlockInterface $layoutBlock */
                            $layoutBlock = $this->admin->getObject($blockId);
                            if ($layoutBlock) {
                                $layoutBlock->setSortOrder($sort);
                                $layoutBlock->setZone($zoneName);
                            }

                            $this->admin->update($layoutBlock);
                        } catch (\Exception $e) {
                            $message = $e->getMessage();

                            return new JsonResponse(array('messageStatus' => 'error', 'message' => $message));
                        }
                    }

                }
            }
        }

        $data = array(
            'messageStatus' => 'success',
            'message' => $this->translate('message.layout_blocks_sorted', array('zone' => ''))
        );

        $page = $pageAdmin->getModelManager()->find($pageAdmin->getClass(), $objectId);

        if ($page) {
            $pageStatus = $this->renderView(
                'NetworkingInitCmsBundle:PageAdmin:page_status_settings.html.twig',
                array(
                    'admin' => $pageAdmin,
                    'object' => $page
                )
            );
            $data['pageStatusSettings'] = $pageStatus;
            $data['pageStatus'] = $this->translate($page->getStatus());
        }

        return new JsonResponse($data);
    }


    /**
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function deleteAjaxAction()
    {
        $request = $this->getRequest();
        $layoutBlockId = $request->get('layoutBlockId');
        $objectId = $request->get('objectId');
        $uniqid = $request->get('uniqid');
        $elementId = $request->get('elementId');

        if ($layoutBlockId) {

            $layoutBlock = $this->admin->getObject($layoutBlockId);
            if ($layoutBlock) {
                $this->admin->delete($layoutBlock);
            }
        }

        $html = $this->getLayoutBlockFormWidget($objectId, $elementId, $uniqid);

        return new JsonResponse(array(
            'messageStatus' => 'success',
            'message' => $this->translate('message.layout_block_deleted'),
            'html' => $html
        ));
    }

    /**
     * Deep sort of array
     * 
     * @param $array
     */
    public function uksort(&$array){
        ksort($array);
        foreach($array as $key => $value){
            if(is_array($value)){
                $this->uksort($value);
                $array[$key] = $value;
            }
        }
    }

    /**
     * extract a clean content array from parameter string
     *
     * @param $contentStr
     * @return array
     */
    public function cleanContentString($contentStr){
        foreach($contentStr as $key => $formId){

            if(array_key_exists('content', $formId)){
                return $formId['content'];
            }
            foreach($formId as $layoutBlock){
                if(!is_array($layoutBlock)){
                    return $formId;
                }
                if(array_key_exists('content', $layoutBlock)){
                    return $layoutBlock['content'];
                }
                foreach($layoutBlock as $contentEl){
                    if(array_key_exists('content', $contentEl)){
                        return $contentEl['content'];
                    }
                }
            }
        }

        return array();
    }
}
