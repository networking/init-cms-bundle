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
use Networking\InitCmsBundle\Component\EventDispatcher\CmsEventDispatcher;
use Networking\InitCmsBundle\Helper\PageHelper;
use Networking\InitCmsBundle\Cache\PageCacheInterface;
use Networking\InitCmsBundle\Model\PageInterface;
use Networking\InitCmsBundle\Model\PageManagerInterface;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Gedmo\Sluggable\Util\Urlizer;

/**
 * Class PageAdminController.
 *
 * @author net working AG <info@networking.ch>
 */
class PageAdminController extends CRUDController
{
    /**
     * @var PageManagerInterface
     */
    protected $pageManager;

    /**
     * @var PageHelper
     */
    protected $pageHelper;

    public function __construct(
        CmsEventDispatcher $dispatcher,
        PageCacheInterface $pageCache,
        PageManagerInterface $pageManager,
        PageHelper $pageHelper
    ) {
        $this->pageManager = $pageManager;
        $this->pageHelper = $pageHelper;
        parent::__construct($dispatcher, $pageCache);
    }

    /**
     * Create a copy of a page in the given local and connect the pages.
     *
     * @param $id
     * @param $locale
     *
     * @return RedirectResponse|Response
     * @throws NotFoundHttpException
     */
    public function translateAction(
        Request $request,
        $id,
        $locale
    ): \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response {
        /** @var PageInterface $page */
        $page = $this->admin->getObject($id);

        if (!$page) {
            throw new NotFoundHttpException(
                sprintf('unable to find the object with id : %s', $id)
            );
        }
        $language = \Locale::getDisplayLanguage($locale);

        if ($request->getMethod() == 'POST') {
//            try {
                $pageCopy = $this->pageHelper->makeTranslationCopy(
                    $page,
                    $locale
                );
                $this->admin->createObjectSecurity($pageCopy);
                $status = Response::HTTP_OK;
                $message = $this->translate(
                    'message.translation_saved',
                    ['%language%' => $language]
                );
                $html = $this->renderView(
                    '@NetworkingInitCms/PageAdmin/page_translation_settings.html.twig',
                    ['object' => $page, 'admin' => $this->admin]
                );
//            } catch (\Exception $e) {
//                $status = Response::HTTP_NOT_ACCEPTABLE;
//                $message = $message = $this->translate(
//                    'message.translation_not_saved',
//                    ['%language%' => $language, '%url%' => $page->getFullPath()]
//                );
//
//                $message = $e->getMessage();
//                $html = '';
//            }

            return $this->renderJson(
                [
                    'html' => $html,
                    'message' => $message,
                ],$status
            );
        }

        $params = $this->addRenderExtraParams([
                'action' => 'copy',
                'page' => $page,
                'id' => $id,
                'locale' => $locale,
                'language' => $language,
                'admin' => $this->admin,
            ]);

        $html = $this->renderView(
            '@NetworkingInitCms/PageAdmin/page_translation_copy.html.twig',
            $params
        );

        return new JsonResponse([
            'html' => $html,
            'status' => 'success',
        ]);
    }


    /**
     * @param $id
     *
     * @return RedirectResponse|Response
     */
    public function copyAction(
        Request $request,
        $id
    ): \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response {
        /** @var PageInterface $page */
        $page = $this->admin->getObject($id);

        if (!$page) {
            throw new NotFoundHttpException(
                sprintf('unable to find the object with id : %s', $id)
            );
        }

        if ($request->getMethod() == 'POST') {
            $pageCopy = false;
            try {
                $pageCopy = $this->pageHelper->makePageCopy($page);
                $this->admin->createObjectSecurity($pageCopy);
                $status = 'success';
                $message = $this->translate(
                    'message.copy_saved',
                    ['%page%' => $pageCopy]
                );
                $result = 'ok';
                $html = $this->renderView(
                    '@NetworkingInitCms/PageAdmin/page_translation_settings.html.twig',
                    ['object' => $page, 'admin' => $this->admin]
                );
            } catch (\Exception) {
                $status = 'error';
                $message = $message = $this->translate(
                    'message.copy_not_saved',
                    ['%page%' => $page, '%url%' => $page->getFullPath()]
                );
                $result = 'error';
                $html = '';
            }

            if ($this->isXmlHttpRequest($request)) {
                return $this->renderJson(
                    [
                        'result' => $result,
                        'status' => $status,
                        'html' => $html,
                        'message' => $message,
                    ]
                );
            }

            $this->addFlash(
                'sonata_flash_'.$status,
                $message
            );

            if ($pageCopy) {
                $request->getSession()->set(
                    'Page.last_edited',
                    $pageCopy->getId()
                );
            }


            return $this->redirect($this->admin->generateUrl('list'));
        }

        return $this->renderWithExtraParams(
            '@NetworkingInitCms/PageAdmin/page_copy.html.twig',
            [
                'language' => \Locale::getDisplayLanguage($page->getLocale()),
                'action' => 'copy',
                'page' => $page,
                'id' => $id,
                'admin' => $this->admin,
            ]
        );
    }

    /**
     * Link pages as translations of each other.
     *
     * @param $id
     * @param $locale
     *
     * @return RedirectResponse|Response
     * @throws NotFoundHttpException
     *
     */
    public function linkAction(
        Request $request,
        $id,
        $locale
    ): \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response {
        /** @var PageInterface $page */
        $page = $this->admin->getObject($id);

        if (!$page) {
            throw new NotFoundHttpException(
                sprintf('unable to find the Page with id : %s', $id)
            );
        }

        if ($request->getMethod() == 'POST') {
            $linkPageId = $request->get('page');
            if (!$linkPageId) {
                $this->addFlash(
                    'sonata_flash_error',
                    $this->translate('flash_link_error')
                );
            } else {
                /** @var PageInterface $linkPage */
                $linkPage = $this->admin->getObject($linkPageId);

                $page->addTranslation($linkPage);

                $this->admin->update($page);

                if ($this->isXmlHttpRequest($request)) {
                    $html = $this->renderView(
                        '@NetworkingInitCms/PageAdmin/page_translation_settings.html.twig',
                        ['object' => $page, 'admin' => $this->admin]
                    );

                    return $this->renderJson(
                        [
                            'result' => 'ok',
                            'html' => $html,
                            'message' => $this->translate('flash_link_success'),
                        ]
                    );
                }


                return new RedirectResponse(
                    $this->admin->generateUrl('edit', ['id' => $page->getId()])
                );
            }
        }

        $pages = $this->admin->getModelManager()->findBy(
            $this->admin->getClass(),
            ['locale' => $locale]
        );

        if (count($pages)) {
            $pages = new ArrayCollection($pages);
            $originalLocale = $page->getLocale();
            $pages = $pages->filter(
                fn(PageInterface $linkPage) => !in_array(
                    $originalLocale,
                    $linkPage->getTranslatedLocales()
                )
            );
        }

        $parans = $this->addRenderExtraParams(
            [
                'page' => $page,
                'pages' => $pages,
                'locale' => $locale,
                'original_language' => \Locale::getDisplayLanguage(
                    $page->getLocale()
                ),
                'language' => \Locale::getDisplayLanguage($locale),
                'admin' => $this->admin,
            ]
        );

        $html = $this->renderView(
            '@NetworkingInitCms/PageAdmin/page_translation_link_list.html.twig',
            $parans
        );

        return $this->renderJson(
            [
                'result' => 'ok',
                'html' => $html,
            ]
        );
    }

    /**
     * @param $id
     * @param $translationId
     *
     * @return RedirectResponse|Response
     * @throws NotFoundHttpException
     */
    public function unlinkAction(
        Request $request,
        $id,
        $translationId
    ): \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response {

        /** @var PageInterface $page */
        $page = $this->admin->getObject($id);
        $translatedPage = $this->admin->getObject($translationId);

        if (!$page) {
            throw new NotFoundHttpException(
                sprintf('unable to find the Page with id : %s', $id)
            );
        }
        $method = $request->get('_method');


        if ($request->getMethod() == 'DELETE') {
            $page->removeTranslation($translatedPage);
            $translatedPage->removeTranslation($page);

            $this->admin->update($page);
            $this->admin->update($translatedPage);

            if ($this->isXmlHttpRequest($request)) {
                $html = $this->renderView(
                    '@NetworkingInitCms/PageAdmin/page_translation_settings.html.twig',
                    ['object' => $page, 'admin' => $this->admin]
                );

                return $this->renderJson(
                    [
                        'status' => 'success',
                        'message' => 'Translation unlinked successfully',
                        'html' => $html,
                    ]
                );
            }

            $this->addFlash(
                'sonata_flash_success',
                $this->translate('flash_unlink_success')
            );

            return new RedirectResponse(
                $this->admin->generateUrl('edit', ['id' => $page->getId()])
            );
        }

        return $this->renderWithExtraParams(
            '@NetworkingInitCms/PageAdmin/page_translation_unlink.html.twig',
            [
                'action' => 'unlink',
                'page' => $page,
                'translationId' => $translationId,
                'admin' => $this->admin,
                'translatedPage' => $translatedPage,
            ]
        );
    }

    /**
     *
     * @return RedirectResponse
     * @throws AccessDeniedException
     */
    public function batchActionPublish(ProxyQueryInterface $selectedModelQuery)
    {
        if ($this->admin->isGranted('PUBLISH') === false) {
            throw new AccessDeniedException();
        }

        $modelManager = $this->admin->getModelManager();

        $selectedModels = $selectedModelQuery->execute();

        // do the merge work here

        try {
            foreach ($selectedModels as $selectedModel) {
                /* @var PageInterface $selectedModel */
                $selectedModel->setStatus(PageInterface::STATUS_PUBLISHED);
                $modelManager->update($selectedModel);
                $this->makeSnapshot($selectedModel);
            }
        } catch (\Exception) {
            $this->addFlash(
                'sonata_flash_error',
                $this->translate(
                    'flash_batch_publish_error',
                    [],
                    'NetworkingInitCmsBundle'
                )
            );

            return new RedirectResponse(
                $this->admin->generateUrl(
                    'list',
                    $this->admin->getFilterParameters()
                )
            );
        }
        $this->addFlash(
            'sonata_flash_success',
            $this->translate(
                'flash_batch_publish_success',
                [],
                'NetworkingInitCmsBundle'
            )
        );

        return new RedirectResponse(
            $this->admin->generateUrl(
                'list',
                $this->admin->getFilterParameters()
            )
        );
    }

    /**
     *
     * @return RedirectResponse
     * @throws AccessDeniedException
     */
    public function batchActionCopy(ProxyQueryInterface $selectedModelQuery)
    {
        if ($this->admin->isGranted('EDIT') === false) {
            throw new AccessDeniedException();
        }


        $selectedModels = $selectedModelQuery->execute();

        try {
            foreach ($selectedModels as $selectedModel) {
                /* @var PageInterface $selectedModel */
                $this->pageHelper->makePageCopy($selectedModel);
            }
        } catch (\Exception) {
            $this->addFlash(
                'sonata_flash_error',
                $this->translate(
                    'flash_batch_copy_error',
                    [],
                    'NetworkingInitCmsBundle'
                )
            );

            return new RedirectResponse(
                $this->admin->generateUrl(
                    'list',
                    $this->admin->getFilterParameters()
                )
            );
        }

        $this->addFlash(
            'sonata_flash_success',
            $this->translate(
                'flash_batch_copy_success',
                [],
                'NetworkingInitCmsBundle'
            )
        );

        return new RedirectResponse(
            $this->admin->generateUrl(
                'list',
                $this->admin->getFilterParameters()
            )
        );
    }

    /**
     * @return Response
     */
    public function batchTranslateAction(Request $request)
    {
        if ($this->admin->isGranted('ROLE_SUPER_ADMIN') === false) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(
            \Networking\InitCmsBundle\Form\Type\PageBatchCopyType::class,
            [],
            [
                'locales' => $this->getParameter(
                    'networking_init_cms.page.languages'
                ),
            ]
        );

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $data = $form->getData();


            $pages = $this->admin->getModelManager()->findBy(
                $this->admin->getClass(),
                ['locale' => $data['fromLocale']]
            );
            /** @var PageInterface $page */
            foreach ($pages as $page) {
                $translatedLocales = $page->getTranslatedLocales();

                if (in_array($data['toLocale'], $translatedLocales)) {
                    continue;
                }
                $this->pageHelper->makeTranslationCopy(
                    $page,
                    $data['toLocale']
                );
            }

            $this->addFlash(
                'sonata_flash_success',
                $this->translate(
                    'flash_batch_copy_success',
                    [],
                    'NetworkingInitCmsBundle'
                )
            );
        }

        return $this->renderWithExtraParams(
            '@NetworkingInitCms/PageAdmin/batch_page_copy.html.twig',
            ['action' => 'batchCopy', 'form' => $form->createView()]
        );
    }

    /**
     *
     * @return RedirectResponse
     * @throws AccessDeniedException
     */
    public function batchActionCacheClear(
        ProxyQueryInterface $selectedModelQuery
    ) {
        if ($this->admin->isGranted('PUBLISH') === false) {
            throw new AccessDeniedException();
        }
        /** @var PageInterface[] $selectedModels */
        $selectedModels = $selectedModelQuery->execute();

        try {
            foreach ($selectedModels as $selectedModel) {
                if ($this->pageCache->isActive()) {
                    $cacheKey = 'page_'.$selectedModel->getLocale().str_replace(
                        '/',
                        '',
                        $selectedModel->getFullPath()
                    );
                    $this->pageCache->delete($cacheKey);
                }
            }
        } catch (\Exception) {
            $this->addFlash(
                'sonata_flash_error',
                $this->translate(
                    'flash_batch_cache_clear_error',
                    [],
                    'NetworkingInitCmsBundle'
                )
            );

            return new RedirectResponse(
                $this->admin->generateUrl(
                    'list',
                    $this->admin->getFilterParameters()
                )
            );
        }

        $this->addFlash(
            'sonata_flash_success',
            $this->translate(
                'flash_batch_cache_clear_success',
                [],
                'NetworkingInitCmsBundle'
            )
        );

        return new RedirectResponse(
            $this->admin->generateUrl(
                'list',
                $this->admin->getFilterParameters()
            )
        );
    }

    /**
     * @param null $id
     *
     * @return Response
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function showAction(Request $request): Response
    {
        $id = null;
        $object = $this->assertObjectExists($request, true);
        \assert(null !== $object);

        $this->checkParentChildAssociation($request, $object);

        $this->admin->checkAccess('show', $object);

        if (!$object) {
            throw new NotFoundHttpException(
                sprintf('unable to find the object with id : %s', $id)
            );
        }

        if (false === $this->admin->isGranted('VIEW', $object)) {
            throw new AccessDeniedException();
        }

        $this->admin->setSubject($object);

        return $this->renderWithExtraParams(
            $this->admin->getTemplateRegistry()->getTemplate('show'),
            [
                'action' => 'show',
                'object' => $object,
                'elements' => $this->admin->getShow(),
            ]
        );
    }

    /**
     * @param null $id
     *
     * @return RedirectResponse|Response
     * @throws \Twig\Error\RuntimeError
     */
    public function editAction(Request $request): Response
    {
        $id = null;
        $object = $this->assertObjectExists($request, true);
        \assert(null !== $object);

        $this->checkParentChildAssociation($request, $object);

        $this->admin->checkAccess('edit', $object);

        if (!$object) {
            throw new NotFoundHttpException(
                sprintf('unable to find the object with id : %s', $id)
            );
        }

        if (false === $this->admin->isGranted('EDIT', $object)) {
            throw new AccessDeniedException();
        }

        $this->admin->setSubject($object);

        /** @var $form \Symfony\Component\Form\Form */
        $form = $this->admin->getForm();
        $form->setData($object);

        if ($request->getMethod() == 'POST') {
            if (!$this->isXmlHttpRequest($request)) {
                throw new NotFoundHttpException('Should only submit over ajax');
            }

            $request->attributes->add(['objectId' => $id]);
            $request->attributes->add(['page_locale' => $object->getLocale()]);

            $form->handleRequest($request);

            if ($form->isValid()) {
                $object->setStatus(PageInterface::STATUS_DRAFT);
                $this->admin->update($object);

                return $this->getAjaxEditResponse($object);
            } else {
                $form->addError(
                    new FormError(
                        $this->translate(
                            'flash_edit_error',
                            [],
                            'NetworkingInitCmsBundle'
                        )
                    )
                );
            }
        }

        $view = $form->createView();

        // set the theme for the current Admin Form
        $this->setFormTheme($view, $this->admin->getFormTheme());

        $rootMenus = $this->admin->getModelManager()->findBy(
            $this->getParameter('networking_init_cms.admin.menu_item.class'),
            ['isRoot' => 1, 'locale' => $object->getLocale()]
        );

        return $this->renderWithExtraParams(
            $this->admin->getTemplateRegistry()->getTemplate('edit'),
            [
                'action' => 'edit',
                'form' => $view,
                'object' => $object,
                'rootMenus' => $rootMenus,
                'language' => \Locale::getDisplayLanguage($object->getLocale()),
            ]
        );
    }

    /**
     * @param $id
     *
     * @return Response
     * @throws \Twig\Error\RuntimeError
     */
    public function editPageSettingsAction(Request $request, $id)
    {
        /** @var PageInterface $page */
        $page = $this->admin->getObject($id);


        $form = $this->admin->getForm();
        $form->setData($page);

        $form->handleRequest($request);

        $response = new Response();

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $page->setStatus(PageInterface::STATUS_DRAFT);
                $page->setUpdatedAt();
                $page = $this->admin->update($page);

                return $this->getAjaxEditResponse($page);
            }
            $response->setStatusCode(422);
        }
        $view = $form->createView();
        $this->setFormTheme($view, $this->admin->getFormTheme());

        return $this->handleXmlHttpRequestErrorResponse($request, $form);

    }

    /**
     * @param null $id
     * @param null $uniqid
     *
     * @return Response
     * @throws \Twig\Error\RuntimeError
     */
    public function pageSettingsAction($id = null, $uniqid = null)
    {
        /** @var Request $request */
        $request = $this->container->get('request_stack')->getCurrentRequest();

        if ($id === null) {
            $id = $request->get($this->admin->getIdParameter());
        }

        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(
                sprintf('unable to find the object with id : %s', $id)
            );
        }

        if (false === $this->admin->isGranted('EDIT', $object)) {
            throw new AccessDeniedException();
        }

        $this->admin->setSubject($object);

        /** @var $form \Symfony\Component\Form\Form */
        $form = $this->admin->getForm();
        $form->setData($object);

        $view = $form->createView();

        // set the theme for the current Admin Form
        $this->setFormTheme($view, $this->admin->getFormTheme());

        return $this->renderWithExtraParams(
            $this->admin->getTemplateRegistry()->getTemplate('edit'),
            [
                'action' => 'edit',
                'form' => $view,
                'object' => $object,
                'language' => \Locale::getDisplayLanguage($object->getLocale()),
            ]
        );
    }

    /**
     * Return the json response for the ajax edit action.
     *
     *
     * @return Response
     *
     * @throws \Twig\Error\RuntimeError
     */
    protected function getAjaxEditResponse(PageInterface $page)
    {

        $pageStatusSettingsHtml = $this->renderView(
            '@NetworkingInitCms/PageAdmin/page_status_settings.html.twig',
            [
                'object' => $page,
                'admin' => $this->admin,
                'admin_pool' => $this->container->get('sonata.admin.pool'),
            ]
        );

        return $this->renderJson(
            [
                'result' => 'ok',
                'objectId' => $page->getId(),
                'title' => $page->__toString(),
                'message' => $this->translate('info.page_settings_updated'),
                'pageStatusSettings' => $pageStatusSettingsHtml,
            ]
        );
    }

    public function getPageStatusAction(Request $request){
        $id = $request->get('id');
        $page = $this->admin->getObject($id);

        return $this->getAjaxEditResponse($page);
    }

    /**
     * @return Response
     */
    public function parentPageListAction(Request $request)
    {
        $locale = $request->get('locale');
        $pages = [];

        if ($result = $this->pageManager->getParentPagesChoices($locale)) {
            foreach ($result as $page) {
                /* @var PageInterface $page */
                $pages[$page->getId()] = [$page->getAdminTitle()];
            }
        }

        return $this->renderJson($pages);
    }

    /**
     *
     * @return RedirectResponse|Response
     * @throws \Twig\Error\RuntimeError
     */
    public function draftAction(Request $request): \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        $id = $request->get($this->admin->getIdParameter());

        return $this->changePageStatus($id, PageInterface::STATUS_DRAFT);
    }

    /**
     *
     * @return RedirectResponse|Response
     * @throws \Twig\Error\RuntimeError
     */
    public function reviewAction(Request $request): \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        $id = $request->get($this->admin->getIdParameter());

        return $this->changePageStatus($id, PageInterface::STATUS_REVIEW);
    }

    /**
     *
     * @return RedirectResponse|Response
     * @throws \Twig\Error\RuntimeError
     */
    public function offlineAction(Request $request): \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
    {
        $id = $request->get($this->admin->getIdParameter());

        $putOnline = $request->query->getBoolean('put-online', false);

        $status = $putOnline ? PageInterface::STATUS_PUBLISHED : PageInterface::STATUS_OFFLINE;

        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(
                sprintf('unable to find the object with id : %s', $id)
            );
        }

        if (false === $this->admin->isGranted('PUBLISH', $object)) {
            throw new AccessDeniedException();
        }

        $this->admin->setSubject($object);

        $form = $this->admin->getForm();

        $object->setStatus($status);

        // persist if the form was valid and if in preview mode the preview was approved
        $this->admin->update($object);

        $this->makeSnapshot($object);

        $this->addFlash(
            'sonata_flash_success',
            $this->translate('flash_status_success')
        );

        return $this->redirect(
            $this->admin->generateObjectUrl('edit', $object)
        );
    }

    /**
     * @param $id
     * @param $status
     *
     * @return RedirectResponse
     */
    protected function changePageStatus($id, $status)
    {
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(
                sprintf('unable to find the object with id : %s', $id)
            );
        }

        if (false === $this->admin->isGranted('EDIT', $object)) {
            throw new AccessDeniedException();
        }

        $this->admin->setSubject($object);

        $form = $this->admin->getForm();

        $object->setStatus($status);

        // persist if the form was valid and if in preview mode the preview was approved
        $this->admin->update($object);

        $this->addFlash(
            'sonata_flash_success',
            $this->translate('flash_status_success')
        );

        return $this->redirect(
            $this->admin->generateObjectUrl('edit', $object)
        );
    }

    /**
     * @param null $id
     *
     * @return RedirectResponse|Response
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function cancelDraftAction(
        Request $request,
        $id = null
    ): \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response {
        /** @var $draftPage PageInterface */
        $draftPage = $this->admin->getObject($id);

        if (!$draftPage) {
            throw new NotFoundHttpException(
                sprintf('unable to find the object with id : %s', $id)
            );
        }

        if (false === $this->admin->isGranted('EDIT', $draftPage)) {
            throw new AccessDeniedException();
        }

        if ($request->getMethod() == 'POST') {
            $serializer = $this->pageHelper->getSerializer();
            $this->pageManager->revertToPublished(
                $draftPage,
                $serializer
            );


            return $this->renderJson(
                [
                    'redirect' => $this->admin->generateObjectUrl('edit', $publishedPage),
                ]
            );

        }

        return $this->renderWithExtraParams(
            '@NetworkingInitCms/PageAdmin/page_cancel_draft.html.twig',
            [
                'action' => 'cancelDraft',
                'page' => $draftPage,
                'admin' => $this->admin,
            ]
        );
    }

    /**
     * @param null $id
     *
     * @return RedirectResponse
     */
    public function publishAction(Request $request, $id = null)
    {
        $id = $request->get($this->admin->getIdParameter());

        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(
                sprintf('unable to find the object with id : %s', $id)
            );
        }

        if (false === $this->admin->isGranted('PUBLISH', $object)) {
            throw new AccessDeniedException();
        }

        $this->admin->setSubject($object);

        $form = $this->admin->getForm();

        $object->setStatus(PageInterface::STATUS_PUBLISHED);

        // persist if the form was valid and if in preview mode the preview was approved
        $this->admin->update($object);

        if ($object->getStatus() == PageInterface::STATUS_PUBLISHED) {
            $this->makeSnapshot($object);
        }

        $this->addFlash(
            'sonata_flash_success',
            $this->translate('flash_publish_success')
        );

        return $this->redirect(
            $this->admin->generateObjectUrl('edit', $object)
        );
    }

    /**
     * Create a snapshot of a published page.
     */
    protected function makeSnapshot(PageInterface $page)
    {
        if (!$this->admin->isGranted('PUBLISH', $page)) {
            return;
        }


        $this->pageHelper->makePageSnapshot($page);
    }

    /**
     * Return a json array with the calculated path for a page object.
     *
     *
     * @return Response
     * @internal param string $path
     *
     */
    public function getPathAction(Request $request)
    {
        $id = $request->get('page_id');

        $getPath = $request->get('path');

        $object = $this->admin->getObject($id);
        if ($id && $object) {
            $path = $object->getFullPath();
        } else {
            $path = '/';
        }

        $getPath = Urlizer::urlize($getPath);

        return $this->renderJson(['path' => $path.$getPath]);
    }
}
