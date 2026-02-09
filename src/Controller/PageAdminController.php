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
use Networking\InitCmsBundle\Util\Urlizer;
use Networking\InitCmsBundle\Cache\PageCacheInterface;
use Networking\InitCmsBundle\Component\EventDispatcher\CmsEventDispatcher;
use Networking\InitCmsBundle\Doctrine\Extensions\Versionable\VersionableInterface;
use Networking\InitCmsBundle\Helper\PageHelper;
use Networking\InitCmsBundle\Model\PageInterface;
use Networking\InitCmsBundle\Model\PageManagerInterface;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
        PageHelper $pageHelper,
    ) {
        $this->pageManager = $pageManager;
        $this->pageHelper = $pageHelper;
        parent::__construct($dispatcher, $pageCache);
    }

    /**
     * Create a copy of a page in the given local and connect the pages.
     *
     * @throws NotFoundHttpException
     */
    public function translateAction(
        Request $request,
        $id,
        $locale,
    ): RedirectResponse|Response {
        /** @var PageInterface $page */
        $page = $this->admin->getObject($id);

        if (!$page) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }
        $language = \Locale::getDisplayLanguage($locale);

        if ('POST' == $request->getMethod()) {
            try {
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
            } catch (\Exception $e) {
                $status = Response::HTTP_NOT_ACCEPTABLE;
                $message = $message = $this->translate(
                    'message.translation_not_saved',
                    ['%language%' => $language, '%url%' => $page->getFullPath()]
                );

                $message = $e->getMessage();
                $html = '';
            }

            return $this->renderJson(
                [
                    'html' => $html,
                    'message' => $message,
                ], $status
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

    public function copyAction(
        Request $request,
        $id,
    ): RedirectResponse|Response {
        /** @var PageInterface $page */
        $page = $this->admin->getObject($id);

        if (!$page) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if ('POST' == $request->getMethod()) {
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
     * @throws NotFoundHttpException
     */
    public function linkAction(
        Request $request,
        $id,
        $locale,
    ): RedirectResponse|Response {
        /** @var PageInterface $page */
        $page = $this->admin->getObject($id);

        if (!$page) {
            throw new NotFoundHttpException(sprintf('unable to find the Page with id : %s', $id));
        }

        if (Request::METHOD_POST == $request->getMethod()) {
            $linkPageId = $request->request->get('page');
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
                fn (PageInterface $linkPage) => !in_array(
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
     * @throws NotFoundHttpException
     */
    public function unlinkAction(
        Request $request,
        $id,
        $translationId,
    ): RedirectResponse|Response {
        /** @var PageInterface $page */
        $page = $this->admin->getObject($id);
        $translatedPage = $this->admin->getObject($translationId);

        if (!$page) {
            throw new NotFoundHttpException(sprintf('unable to find the Page with id : %s', $id));
        }

        if (Request::METHOD_DELETE == $request->getMethod()) {
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
     * @return RedirectResponse
     *
     * @throws AccessDeniedException
     */
    public function batchActionPublish(ProxyQueryInterface $selectedModelQuery)
    {
        if (false === $this->admin->isGranted('PUBLISH')) {
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
     * @return RedirectResponse
     *
     * @throws AccessDeniedException
     */
    public function batchActionCopy(ProxyQueryInterface $selectedModelQuery)
    {
        if (false === $this->admin->isGranted('EDIT')) {
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
        if (false === $this->admin->isGranted('ROLE_SUPER_ADMIN')) {
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
     * @return RedirectResponse
     *
     * @throws AccessDeniedException
     */
    public function batchActionCacheClear(
        ProxyQueryInterface $selectedModelQuery,
    ) {
        if (false === $this->admin->isGranted('PUBLISH')) {
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
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     */
    public function showAction(Request $request): Response
    {
        $id = null;
        $object = $this->assertObjectExists($request, true);
        \assert(null !== $object);

        $this->checkParentChildAssociation($request, $object);

        $this->admin->checkAccess('show', $object);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
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
     * @return RedirectResponse|Response
     *
     * @throws \Twig\Error\RuntimeError
     */
    public function editAction(Request $request): Response
    {
        $id = null;
        $existingObject = $this->assertObjectExists($request, true);
        \assert(null !== $existingObject);

        $this->checkParentChildAssociation($request, $existingObject);

        $this->admin->checkAccess('edit', $existingObject);

        $preResponse = $this->preEdit($request, $existingObject);
        if (null !== $preResponse) {
            return $preResponse;
        }

        $this->admin->setSubject($existingObject);

        $objectId = $this->admin->getNormalizedIdentifier($existingObject);
        \assert(null !== $objectId);

        /** @var $form \Symfony\Component\Form\Form */
        $form = $this->admin->getForm();
        $form->setData($existingObject);

        if ('POST' == $request->getMethod()) {
            if (!$this->isXmlHttpRequest($request)) {
                throw new NotFoundHttpException('Should only submit over ajax');
            }

            $request->attributes->add(['objectId' => $id]);
            $request->attributes->add(['page_locale' => $existingObject->getLocale()]);

            $form->handleRequest($request);

            if ($form->isSubmitted()) {
                if ($form->isValid()) {
                    $submittedObject = $form->getData();
                    $this->admin->setSubject($submittedObject);

                    $existingObject->setStatus(VersionableInterface::STATUS_DRAFT);
                    $this->admin->update($submittedObject);

                    return $this->getAjaxEditResponse($submittedObject, $form);
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
        }

        if ('GET' == $request->getMethod() && $this->isXmlHttpRequest($request)) {
            return $this->getAjaxEditResponse($existingObject, $form);
        }

        $view = $form->createView();

        // set the theme for the current Admin Form
        $this->setFormTheme($view, $this->admin->getFormTheme());

        $rootMenus = $this->admin->getModelManager()->findBy(
            $this->getParameter('networking_init_cms.admin.menu_item.class'),
            ['isRoot' => 1, 'locale' => $existingObject->getLocale()]
        );

        return $this->renderWithExtraParams(
            $this->admin->getTemplateRegistry()->getTemplate('edit'),
            [
                'action' => 'edit',
                'form' => $view,
                'object' => $existingObject,
                'objectId' => $objectId,
                'rootMenus' => $rootMenus,
                'language' => \Locale::getDisplayLanguage($existingObject->getLocale()),
            ]
        );
    }

    /**
     * @return Response
     *
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

                return $this->getAjaxEditResponse($page, $form);
            }
            $response->setStatusCode(422);
        }
        $view = $form->createView();
        $this->setFormTheme($view, $this->admin->getFormTheme());

        return $this->handleXmlHttpRequestErrorResponse($request, $form);
    }

    /**
     * Return the json response for the ajax edit action.
     *
     * @return Response
     *
     * @throws \Twig\Error\RuntimeError
     */
    protected function getAjaxEditResponse(PageInterface $page, ?FormInterface $form = null)
    {
        $pageStatusSettingsHtml = $this->renderView(
            '@NetworkingInitCms/PageAdmin/page_status_settings.html.twig',
            [
                'object' => $page,
                'admin' => $this->admin,
                'admin_pool' => $this->container->get('sonata.admin.pool'),
            ]
        );

        $layoutBlockSettingsHtml = $form ? $this->renderView(
            '@NetworkingInitCms/PageAdmin/page_edit_layout_blocks.html.twig',
            [
                'form' => $form->createView(),
                'admin' => $this->admin,
            ]
        ) : '';

        return $this->renderJson(
            [
                'result' => 'ok',
                'objectId' => $page->getId(),
                'title' => $page->__toString(),
                'message' => $this->translate('info.page_settings_updated'),
                'pageStatusSettings' => $pageStatusSettingsHtml,
                'layoutBlockSettingsHtml' => $layoutBlockSettingsHtml,
            ]
        );
    }

    public function getPageStatusAction(Request $request)
    {
        $id = $request->attributes->get('id');
        $page = $this->admin->getObject($id);

        return $this->getAjaxEditResponse($page);
    }


    /**
     * @throws \Twig\Error\RuntimeError
     */
    public function draftAction(Request $request): RedirectResponse|Response
    {
        $id = $request->attributes->get($this->admin->getIdParameter());

        return $this->changePageStatus($id, PageInterface::STATUS_DRAFT);
    }

    /**
     * @throws \Twig\Error\RuntimeError
     */
    public function reviewAction(Request $request): RedirectResponse|Response
    {
        $id = $request->attributes->get($this->admin->getIdParameter());

        return $this->changePageStatus($id, PageInterface::STATUS_REVIEW);
    }

    /**
     * @throws \Twig\Error\RuntimeError
     */
    public function offlineAction(Request $request): RedirectResponse|Response
    {
        $id = $request->attributes->get($this->admin->getIdParameter());

        $putOnline = $request->query->getBoolean('put-online', false);

        $status = $putOnline ? PageInterface::STATUS_PUBLISHED : PageInterface::STATUS_OFFLINE;

        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
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
     * @return RedirectResponse
     */
    protected function changePageStatus($id, $status)
    {
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
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
     * @throws AccessDeniedException
     * @throws NotFoundHttpException
     */
    public function cancelDraftAction(
        Request $request,
        $id = null,
    ): RedirectResponse|Response {
        /** @var $draftPage PageInterface */
        $draftPage = $this->admin->getObject($id);

        if (!$draftPage) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('EDIT', $draftPage)) {
            throw new AccessDeniedException();
        }

        if ('POST' == $request->getMethod()) {
            $serializer = $this->pageHelper->getSerializer();
            $this->pageManager->cancelDraft(
                $draftPage,
                $serializer
            );

            return $this->renderJson(
                [
                    'redirect' => $this->admin->generateObjectUrl('edit', $draftPage),
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
        $id = $request->attributes->get($this->admin->getIdParameter());

        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('PUBLISH', $object)) {
            throw new AccessDeniedException();
        }

        $this->admin->setSubject($object);

        $form = $this->admin->getForm();

        $object->setStatus(PageInterface::STATUS_PUBLISHED);

        // persist if the form was valid and if in preview mode the preview was approved
        $this->admin->update($object);

        if (PageInterface::STATUS_PUBLISHED == $object->getStatus()) {
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
     * @return Response
     *
     * @internal param string $path
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
