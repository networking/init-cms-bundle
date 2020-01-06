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

use Doctrine\Common\Collections\ArrayCollection;
use Networking\InitCmsBundle\Component\EventDispatcher\CmsEventDispatcher;
use Networking\InitCmsBundle\Helper\PageHelper;
use Networking\InitCmsBundle\Cache\PageCacheInterface;
use Networking\InitCmsBundle\Model\PageInterface;
use Networking\InitCmsBundle\Model\PageManagerInterface;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
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
     * @param Request $request
     * @param $id
     * @param $locale
     *
     * @return RedirectResponse|Response
     *
     * @throws NotFoundHttpException
     */
    public function translateAction(Request $request, $id, $locale)
    {
        /** @var PageInterface $page */
        $page = $this->admin->getObject($id);

        if (!$page) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }
        $language = \Locale::getDisplayLanguage($locale);

        if ($request->getMethod() == 'POST') {

            try {
                $pageCopy = $this->pageHelper->makeTranslationCopy($page, $locale);
                $this->admin->createObjectSecurity($pageCopy);
                $status = 'success';
                $message = $this->translate(
                    'message.translation_saved',
                    ['%language%' => $language]
                );
                $result = 'ok';
                $html = $this->renderView(
                    '@NetworkingInitCms/PageAdmin/page_translation_settings.html.twig',
                    ['object' => $page, 'admin' => $this->admin]
                );
            } catch (\Exception $e) {
                $status = 'error';
                $message = $message = $this->translate(
                    'message.translation_not_saved',
                    ['%language%' => $language, '%url%' => $page->getFullPath()]
                );
                $result = 'error';
                $html = '';
            }

            if ($this->isXmlHttpRequest()) {
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

            return $this->redirect($this->admin->generateUrl('edit', ['id' => $id]));
        }

        return $this->render(
            '@NetworkingInitCms/PageAdmin/page_translation_copy.html.twig',
            [
                'action' => 'copy',
                'page' => $page,
                'id' => $id,
                'locale' => $locale,
                'language' => $language,
                'admin' => $this->admin,
            ]
        );
    }


    /**
     * @param Request $request
     * @param $id
     *
     * @return RedirectResponse|Response
     */
    public function copyAction(Request $request, $id)
    {
        /** @var PageInterface $page */
        $page = $this->admin->getObject($id);

        if (!$page) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
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
            } catch (\Exception $e) {
                $status = 'error';
                $message = $message = $this->translate(
                    'message.copy_not_saved',
                    ['%page%' => $page, '%url%' => $page->getFullPath()]
                );
                $result = 'error';
                $html = '';
            }

            if ($this->isXmlHttpRequest()) {
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
                $request->getSession()->set('Page.last_edited', $pageCopy->getId());
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
     * @param Request $request
     * @param $id
     * @param $locale
     *
     * @return RedirectResponse|Response
     * @throws NotFoundHttpException
     *
     */
    public function linkAction(Request $request, $id, $locale)
    {
        /** @var PageInterface $page */
        $page = $this->admin->getObject($id);

        if (!$page) {
            throw new NotFoundHttpException(sprintf('unable to find the Page with id : %s', $id));
        }

        if ($request->getMethod() == 'POST') {
            $linkPageId = $request->get('page');
            if (!$linkPageId) {
                $this->addFlash('sonata_flash_error', $this->translate('flash_link_error'));
            } else {
                /** @var PageInterface $linkPage */
                $linkPage = $this->admin->getObject($linkPageId);

                $page->addTranslation($linkPage);

                $this->admin->update($page);

                if ($this->isXmlHttpRequest()) {
                    $html = $this->renderView(
                        '@NetworkingInitCms/PageAdmin/page_translation_settings.html.twig',
                        ['object' => $page, 'admin' => $this->admin]
                    );

                    return $this->renderJson(
                        [
                            'result' => 'ok',
                            'html' => $html,
                        ]
                    );
                }

                $this->addFlash('sonata_flash_success', $this->translate('flash_link_success'));

                return new RedirectResponse($this->admin->generateUrl('edit', ['id' => $page->getId()]));
            }
        }

        $pages = $this->admin->getModelManager()->findBy($this->admin->getClass(), ['locale' => $locale]);

        if (count($pages)) {
            $pages = new ArrayCollection($pages);
            $originalLocale = $page->getLocale();
            $pages = $pages->filter(
                function (PageInterface $linkPage) use ($originalLocale) {
                    return !in_array($originalLocale, $linkPage->getTranslatedLocales());
                }
            );
        }

        return $this->renderWithExtraParams(
            '@NetworkingInitCms/PageAdmin/page_translation_link_list.html.twig',
            [
                'page' => $page,
                'pages' => $pages,
                'locale' => $locale,
                'original_language' => \Locale::getDisplayLanguage($page->getLocale()),
                'language' => \Locale::getDisplayLanguage($locale),
                'admin' => $this->admin,
            ]
        );
    }

    /**
     * @param Request $request
     * @param $id
     * @param $translationId
     *
     * @return RedirectResponse|Response
     *
     * @throws NotFoundHttpException
     */
    public function unlinkAction(Request $request, $id, $translationId)
    {

        /** @var PageInterface $page */
        $page = $this->admin->getObject($id);
        $translatedPage = $this->admin->getObject($translationId);

        if (!$page) {
            throw new NotFoundHttpException(sprintf('unable to find the Page with id : %s', $id));
        }

        if ($request->getMethod() == 'DELETE') {
            $page->removeTranslation($translatedPage);
            $translatedPage->removeTranslation($page);

            $this->admin->update($page);
            $this->admin->update($translatedPage);

            if ($this->isXmlHttpRequest()) {
                $html = $this->renderView(
                    '@NetworkingInitCms/PageAdmin/page_translation_settings.html.twig',
                    ['object' => $page, 'admin' => $this->admin]
                );

                return $this->renderJson(
                    [
                        'result' => 'ok',
                        'html' => $html,
                    ]
                );
            }

            $this->addFlash('sonata_flash_success', $this->translate('flash_unlink_success'));

            return new RedirectResponse($this->admin->generateUrl('edit', ['id' => $page->getId()]));
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
     * @param ProxyQueryInterface $selectedModelQuery
     *
     * @return RedirectResponse
     *
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
        } catch (\Exception $e) {
            $this->addFlash('sonata_flash_error', $this->translate('flash_batch_publish_error', [], 'NetworkingInitCmsBundle'));

            return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
        }
        $this->addFlash('sonata_flash_success', $this->translate('flash_batch_publish_success', [], 'NetworkingInitCmsBundle'));

        return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
    }

    /**
     * @param ProxyQueryInterface $selectedModelQuery
     *
     * @return RedirectResponse
     *
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
        } catch (\Exception $e) {
            $this->addFlash('sonata_flash_error', $this->translate('flash_batch_copy_error', [], 'NetworkingInitCmsBundle'));

            return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
        }

        $this->addFlash('sonata_flash_success', $this->translate('flash_batch_copy_success', [], 'NetworkingInitCmsBundle'));

        return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function batchTranslateAction(Request $request)
    {
        if ($this->admin->isGranted('ROLE_SUPER_ADMIN') === false) {
            throw new AccessDeniedException();
        }

        $form = $this->createForm(
            'Networking\InitCmsBundle\Form\Type\PageBatchCopyType',
            [],
            ['locales' => $this->getParameter('networking_init_cms.page.languages')]
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
                $this->pageHelper->makeTranslationCopy($page, $data['toLocale']);
            }

            $this->addFlash('sonata_flash_success', $this->translate('flash_batch_copy_success', [], 'NetworkingInitCmsBundle'));
        }

        return $this->renderWithExtraParams(
            '@NetworkingInitCms/PageAdmin/batch_page_copy.html.twig',
            ['action' => 'batchCopy', 'form' => $form->createView()]
        );
    }

    /**
     * @param ProxyQueryInterface $selectedModelQuery
     *
     * @return RedirectResponse
     *
     * @throws AccessDeniedException
     */
    public function batchActionCacheClear(ProxyQueryInterface $selectedModelQuery)
    {
        if ($this->admin->isGranted('PUBLISH') === false) {
            throw new AccessDeniedException();
        }

        $selectedModels = $selectedModelQuery->execute();

        try {
            foreach ($selectedModels as $selectedModel) {
                if ($this->pageCache->isActive()) {
                    /** @var PageInterface $selectedModel */
                    $cacheKey = $selectedModel->getLocale().$selectedModel->getFullPath();
                    $this->pageCache->delete($cacheKey);
                }
            }
        } catch (\Exception $e) {
            $this->addFlash('sonata_flash_error', $this->translate('flash_batch_cache_clear_error', [], 'NetworkingInitCmsBundle'));

            return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
        }

        $this->addFlash('sonata_flash_success', $this->translate('flash_batch_cache_clear_success', [], 'NetworkingInitCmsBundle'));

        return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
    }

    /**
     * @param null $id
     *
     * @return Response
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function showAction($id = null)
    {
        /** @var Request $request */
        $request = $this->get('request_stack')->getCurrentRequest();

        $id = $request->get($this->admin->getIdParameter());

        /** @var PageInterface $object */
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('VIEW', $object)) {
            throw new AccessDeniedException();
        }

        $this->admin->setSubject($object);

        return $this->renderWithExtraParams(
            $this->templateRegistry->getTemplate('show'),
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
     * @throws \Twig_Error_Runtime
     */
    public function editAction($id = null)
    {
        /** @var Request $request */
        $request = $this->getRequest();

        if ($id === null) {
            $id = $request->get($this->admin->getIdParameter());
        }

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

        if ($request->getMethod() == 'POST') {
            if (!$this->isXmlHttpRequest()) {
                throw new NotFoundHttpException('Should only submit over ajax');
            }

            $request->attributes->add(['objectId' => $id]);
            $request->attributes->add(['page_locale' => $object->getLocale()]);

            $form->handleRequest($request);

            if ($form->isValid()) {
                $object->setStatus(PageInterface::STATUS_DRAFT);
                $this->admin->update($object);

                return $this->getAjaxEditResponse($form, $object);
            } else {
                $form->addError(new FormError($this->translate('flash_edit_error', [], 'NetworkingInitCmsBundle')));
            }
        }

        $view = $form->createView();

        // set the theme for the current Admin Form
        $this->setFormTheme($view, $this->admin->getFormTheme());

        $rootMenus = $this->admin->getModelManager()->findBy(
            'NetworkingInitCmsBundle:MenuItem',
            ['isRoot' => 1, 'locale' => $object->getLocale()]
        );

        return $this->renderWithExtraParams(
            $this->templateRegistry->getTemplate('edit'),
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
     * @param Request $request
     * @param $id
     *
     * @return Response
     * @throws \Twig_Error_Runtime
     */
    public function editPageSettingsAction(Request $request, $id)
    {
        /** @var PageInterface $page */
        $page = $this->admin->getObject($id);

        $layoutBlocks = $page->getLayoutBlock();

        $form = $this->admin->getForm();
        $form->setData($page);

        $form->handleRequest($request);

        $response = new Response();

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $page->setStatus(PageInterface::STATUS_DRAFT);
                $page->setUpdatedAt();
                $page->setLayoutBlock($layoutBlocks);
                $this->admin->update($page);

                return $this->getAjaxEditResponse($form, $page);

            }
            $response->setStatusCode(422);
        }
        $view = $form->createView();
        $this->setFormTheme($view, $this->admin->getFormTheme());

        return $this->renderWithExtraParams(
            '@NetworkingInitCms/PageAdmin/page_settings_edit.html.twig',
            [
                'form' => $view,
                'object' => $page,
            ],
            $response
        );
    }

    /**
     * @param null $id
     * @param null $uniqid
     *
     * @return Response
     * @throws \Twig_Error_Runtime
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
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
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
            $this->templateRegistry->getTemplate('edit'),
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
     * @param Form $form
     * @param PageInterface $page
     *
     * @return Response
     *
     * @throws \Twig_Error_Runtime
     */
    protected function getAjaxEditResponse(Form $form, PageInterface $page)
    {
        $view = $form->createView();

        // set the theme for the current Admin Form
        $this->setFormTheme($view, $this->admin->getFormTheme());

        $pageSettingsHtml = $this->renderView(
            '@NetworkingInitCms/PageAdmin/page_settings_fields.html.twig',
            [
                'action' => 'edit',
                'form' => $view,
                'object' => $page,
                'admin' => $this->admin,
                'admin_pool' => $this->get('sonata.admin.pool'),
            ]
        );

        $pageStatusSettingsHtml = $this->renderView(
            '@NetworkingInitCms/PageAdmin/page_status_settings.html.twig',
            [
                'action' => 'edit',
                'form' => $view,
                'object' => $page,
                'admin' => $this->admin,
                'admin_pool' => $this->get('sonata.admin.pool'),
            ]
        );

        return $this->renderJson(
            [
                'result' => 'ok',
                'objectId' => $page->getId(),
                'title' => $page->__toString(),
                'messageStatus' => 'success',
                'message' => $this->translate('info.page_settings_updated'),
                'pageStatus' => $this->translate($page->getStatus()),
                'pageStatusSettings' => $pageStatusSettingsHtml,
                'pageSettings' => $pageSettingsHtml,
            ]
        );
    }

    /**
     * @param Request $request
     *
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
     * @param Request $request
     *
     * @return RedirectResponse|Response
     * @throws \Twig_Error_Runtime
     */
    public function draftAction(Request $request)
    {
        $id = $request->get($this->admin->getIdParameter());

        return $this->changePageStatus($id, PageInterface::STATUS_DRAFT);
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     * @throws \Twig_Error_Runtime
     */
    public function reviewAction(Request $request)
    {
        $id = $request->get($this->admin->getIdParameter());

        return $this->changePageStatus($id, PageInterface::STATUS_REVIEW);
    }

    /**
     * @param Request $request
     *
     * @return RedirectResponse|Response
     * @throws \Twig_Error_Runtime
     */
    public function offlineAction(Request $request)
    {
        $id = $request->get($this->admin->getIdParameter());

        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('PUBLISH', $object)) {
            throw new AccessDeniedException();
        }

        $this->admin->setSubject($object);

        $form = $this->admin->getForm();

        $object->setStatus(PageInterface::STATUS_OFFLINE);

        // persist if the form was valid and if in preview mode the preview was approved
        $this->admin->update($object);

        if ($object->getStatus() == PageInterface::STATUS_OFFLINE) {
            $this->makeSnapshot($object);
        }

        $this->addFlash(
            'sonata_flash_success',
            $this->translate('flash_status_success')
        );

        return $this->redirect($this->admin->generateObjectUrl('edit', $object));
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

        $this->addFlash('sonata_flash_success', $this->translate('flash_status_success'));

        return $this->redirect($this->admin->generateObjectUrl('edit', $object));
    }

    /**
     * @param Request $request
     * @param null $id
     *
     * @return RedirectResponse|Response
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function cancelDraftAction(Request $request, $id = null)
    {
        /** @var $draftPage PageInterface */
        $draftPage = $this->admin->getObject($id);

        if (!$draftPage) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('EDIT', $draftPage)) {
            throw new AccessDeniedException();
        }

        if ($request->getMethod() == 'POST') {
            $serializer = $this->get('jms_serializer');

            $publishedPage = $this->pageManager->revertToPublished($draftPage, $serializer);

            if ($request->isXmlHttpRequest()) {
                $form = $this->admin->getForm();
                $form->setData($publishedPage);

                $pageSettingsTemplate = $this->render(
                    $this->templateRegistry->getTemplate('edit'),
                    [
                        'action' => 'edit',
                        'form' => $form->createView(),
                        'object' => $publishedPage,
                    ]
                );

                return $this->renderJson(
                    [
                        'result' => 'ok',
                        'objectId' => $this->admin->getNormalizedIdentifier($publishedPage),
                        'title' => $publishedPage->__toString(),
                        'pageStatus' => $this->translate($publishedPage->getStatus()),
                        'pageSettings' => $pageSettingsTemplate->getContent(),
                    ]
                );
            }

            return $this->redirect($this->admin->generateObjectUrl('edit', $publishedPage));
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
     * @param Request $request
     * @param null $id
     *
     * @return RedirectResponse
     */
    public function publishAction(Request $request, $id = null)
    {
        $id = $request->get($this->admin->getIdParameter());

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

        if ($object->getStatus() == PageInterface::STATUS_PUBLISHED) {
            $this->makeSnapshot($object);
        }

        $this->addFlash(
            'sonata_flash_success',
            $this->translate('flash_publish_success')
        );

        return $this->redirect($this->admin->generateObjectUrl('edit', $object));
    }

    /**
     * Create a snapshot of a published page.
     *
     * @param PageInterface $page
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
     * @param Request $request
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
