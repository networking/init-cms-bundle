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
use Networking\InitCmsBundle\Model\PageInterface;
use Symfony\Component\Form\Form;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Gedmo\Sluggable\Util\Urlizer;

/**
 * Class PageAdminController
 * @package Networking\InitCmsBundle\Controller
 *
 * @author net working AG <info@networking.ch>
 */
class PageAdminController extends CRUDController
{

    /**
     * Create a copy of a page in the given local and connect the pages
     *
     * @param Request $request
     * @param $id
     * @param $locale
     *
     * @return RedirectResponse|Response
     * @throws NotFoundHttpException
     */
    public function translatePageAction(Request $request, $id, $locale)
    {
        /** @var PageInterface $page */
        $page = $this->admin->getObject($id);

        if (!$page) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }
        $language = \Locale::getDisplayLanguage($locale);

        if ($request->getMethod() == 'POST') {

            $pageHelper = $this->container->get('networking_init_cms.helper.page_helper');

            try {

                $pageCopy = $pageHelper->makeTranslationCopy($page, $locale);
                $this->admin->createObjectSecurity($pageCopy);
                $status = 'success';
                $message = $this->translate(
                    'message.translation_saved',
                    array('%language%' => $language)
                );
                $result = 'ok';
                $html = $this->renderView(
                    'NetworkingInitCmsBundle:PageAdmin:page_translation_settings.html.twig',
                    array('object' => $page, 'admin' => $this->admin)
                );
            } catch (\Exception $e) {
                $status = 'error';
                $message = $message = $this->translate(
                    'message.translation_not_saved',
                    array('%language%' => $language, '%url%' => $page->getFullPath())
                );
                $result = 'error';
                $html = '';
            }

            if ($this->isXmlHttpRequest()) {
                return $this->renderJson(
                    array(
                        'result' => $result,
                        'status' => $status,
                        'html' => $html,
                        'message' => $message
                    )
                );
            }

            $this->get('session')->getFlashBag()->add(
                'sonata_flash_' . $status,
                $message
            );

            return $this->redirect($this->admin->generateUrl('edit', array('id' => $id)));
        }

        return $this->render(
            'NetworkingInitCmsBundle:PageAdmin:page_translation_copy.html.twig',
            array(
                'action' => 'copy',
                'page' => $page,
                'id' => $id,
                'locale' => $locale,
                'language' => $language,
                'admin' => $this->admin
            )
        );
    }


    /**
     * Link pages as translations of each other
     *
     * @param Request $request
     * @param $id
     * @param $locale
     * @throws NotFoundHttpException
     * @return RedirectResponse|Response
     */
    public function linkAction(Request $request, $id, $locale)
    {
        /** @var PageInterface $page */
        $page = $this->admin->getObject($id);

        if (!$page) {
            throw new NotFoundHttpException(sprintf('unable to find the Page with id : %s', $id));
        }

        if ($request->getMethod() == 'POST') {

            $linkPageId = $this->getRequest()->get('page');
            if (!$linkPageId) {
                $this->get('session')->getFlashBag()->add('sonata_flash_error', 'flash_link_error');
            } else {
                /** @var PageInterface $linkPage */
                $linkPage = $this->admin->getObject($linkPageId);

                $page->addTranslation($linkPage);

                $this->admin->update($page);

                if ($this->isXmlHttpRequest()) {

                    $html = $this->renderView(
                        'NetworkingInitCmsBundle:PageAdmin:page_translation_settings.html.twig',
                        array('object' => $page, 'admin' => $this->admin)
                    );

                    return $this->renderJson(
                        array(
                            'result' => 'ok',
                            'html' => $html
                        )
                    );
                }

                $this->get('session')->getFlashBag()->add('sonata_flash_success', 'flash_link_success');

                return new RedirectResponse($this->admin->generateUrl('edit', array('id' => $page->getId())));
            }
        }

        $pages = $this->admin->getModelManager()->findBy($this->admin->getClass(), array('locale' => $locale));

        if (count($pages)) {
            $pages = new ArrayCollection($pages);
            $originalLocale = $page->getLocale();
            $pages = $pages->filter(
                function (PageInterface $linkPage) use ($originalLocale) {
                    return !in_array($originalLocale, $linkPage->getTranslatedLocales());

                }
            );
        }

        return $this->render(
            'NetworkingInitCmsBundle:PageAdmin:page_translation_link_list.html.twig',
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
     * @param Request $request
     * @param $id
     * @param $translationId
     * @return RedirectResponse|Response
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
                    'NetworkingInitCmsBundle:PageAdmin:page_translation_settings.html.twig',
                    array('object' => $page, 'admin' => $this->admin)
                );

                return $this->renderJson(
                    array(
                        'result' => 'ok',
                        'html' => $html
                    )
                );
            }


            $this->get('session')->getFlashBag()->add('sonata_flash_success', 'flash_link_success');

            return new RedirectResponse($this->admin->generateUrl('edit', array('id' => $page->getId())));
        }

        return $this->render(
            'NetworkingInitCmsBundle:PageAdmin:page_translation_unlink.html.twig',
            array(
                'action' => 'unlink',
                'page' => $page,
                'translationId' => $translationId,
                'admin' => $this->admin,
                'translatedPage' => $translatedPage
            )
        );


    }

    /**
     * @param ProxyQueryInterface $selectedModelQuery
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
                /** @var PageInterface $selectedModel */
                $selectedModel->setStatus(PageInterface::STATUS_PUBLISHED);
                $modelManager->update($selectedModel);
                $this->makeSnapshot($selectedModel);
            }

        } catch (\Exception $e) {
            $this->get('session')->getFlashBag()->add('sonata_flash_error', 'flash_batch_publish_error');

            return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
        }

        $this->get('session')->getFlashBag()->add('sonata_flash_success', 'flash_batch_publish_success');

        return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
    }

    /**
     * @param ProxyQueryInterface $selectedModelQuery
     * @return RedirectResponse
     * @throws AccessDeniedException
     */
    public function batchActionCacheClear(ProxyQueryInterface $selectedModelQuery)
    {
        if ($this->admin->isGranted('PUBLISH') === false) {
            throw new AccessDeniedException();
        }

        $modelManager = $this->admin->getModelManager();

        $selectedModels = $selectedModelQuery->execute();


        // do the merge work here

        try {
            foreach ($selectedModels as $selectedModel) {
                /** @var \Networking\InitCmsBundle\Lib\PhpCacheInterface $phpCache */
                $phpCache = $this->get('networking_init_cms.lib.php_cache');
                if($phpCache->isActive()){
                    /** @var PageInterface $selectedModel */
                    $cacheKey = $selectedModel->getLocale().$selectedModel->getFullPath();
                    $phpCache->delete($cacheKey);
                }
            }

        } catch (\Exception $e) {
            $this->get('session')->getFlashBag()->add('sonata_flash_error', 'flash_batch_cache_clear_error');

            return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
        }

        $this->get('session')->getFlashBag()->add('sonata_flash_success', 'flash_batch_cache_clear_success');

        return new RedirectResponse($this->admin->generateUrl('list', $this->admin->getFilterParameters()));
    }

    /**
     * @param null $id
     * @return Response
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function showAction($id = null)
    {
        $request = $this->getRequest();

        $id = $request->get($this->admin->getIdParameter());

        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('VIEW', $object)) {
            throw new AccessDeniedException();
        }

        $this->admin->setSubject($object);

        return $this->render(
            $this->admin->getTemplate('show'),
            array(
                'action' => 'show',
                'object' => $object,
                'elements' => $this->admin->getShow(),
            )
        );
    }

    /**
     * @param null $id
     * @return Response
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function editAction($id = null)
    {
        /** @var Request $request */
        $request = $this->container->get('request_stack')->getCurrentRequest();
        // the key used to lookup the template
        $templateKey = 'edit';

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
            if(!$this->isXmlHttpRequest()){
                throw new NotFoundHttpException('Should only submit over ajax');
            }

            $request->attributes->add(array('objectId' => $id));
            $request->attributes->add(array('page_locale' => $object->getLocale()));

            $form->submit($request);

            if ($form->isValid()) {
                $object->setStatus(PageInterface::STATUS_DRAFT);
                $this->admin->update($object);

                return $this->getAjaxEditResponse($form, $object);

            } else {
                $form->addError(new FormError($this->admin->trans('flash_edit_error', array(), 'NetworkingInitCmsBundle')));
            }
        }

        $view = $form->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('form')->renderer->setTheme($view, $this->admin->getFormTheme());


        $rootMenus = $this->admin->getModelManager()->findBy(
            'NetworkingInitCmsBundle:MenuItem',
            array('isRoot' => 1, 'locale' => $object->getLocale())
        );

        return $this->render(
            $this->admin->getTemplate($templateKey),
            array(
                'action' => 'edit',
                'form' => $view,
                'object' => $object,
                'rootMenus' => $rootMenus,
                'language' => \Locale::getDisplayLanguage($object->getLocale())
            )
        );
    }

    /**
     * Return the json response for the ajax edit action
     *
     * @param Form $form
     * @param PageInterface $page
     * @return Response
     */
    protected function getAjaxEditResponse(Form $form, PageInterface $page){
        $view = $form->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('form')->renderer->setTheme($view, $this->admin->getFormTheme());

        $pageSettingsHtml = $this->renderView(
            'NetworkingInitCmsBundle:PageAdmin:ajax_page_settings.html.twig',
            array(
                'action' => 'edit',
                'form' => $view,
                'object' => $page,
                'admin' => $this->admin,
                'admin_pool' => $this->get('sonata.admin.pool')
            )
        );

        return $this->renderJson(
            array(
                'result' => 'ok',
                'objectId' => $page->getId(),
                'title' => $page->__toString(),
                'messageStatus' => 'success',
                'message' => $this->admin->trans('info.page_settings_updated'),
                'pageStatus' => $this->admin->trans($page->getStatus()),
                'pageSettings' => $pageSettingsHtml
            )
        );
    }

    /**
     * @param Request $request
     * @return Response
     */
    public function parentPageListAction(Request $request)
    {
        $locale = $request->get('locale');
        $pages = array();
        $pageManager = $this->get('networking_init_cms.page_manager');

        if ($result = $pageManager->getParentPagesChoices($locale)) {
            foreach ($result as $page) {
                /** @var PageInterface $page */
                $pages[$page->getId()] = array($page->getAdminTitle());
            }
        }

        return $this->renderJson($pages);
    }

    /**
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function draftAction(Request $request)
    {
        $id = $request->get($this->admin->getIdParameter());

        return $this->changePageStatus($id, PageInterface::STATUS_DRAFT);
    }

    /**
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function reviewAction(Request $request)
    {
        $id = $request->get($this->admin->getIdParameter());

        return $this->changePageStatus($id, PageInterface::STATUS_REVIEW);
    }

    /**
     * @param $id
     * @param $status
     * @return RedirectResponse|Response
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
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


        if ($this->isXmlHttpRequest()) {

            $view = $form->createView();

            // set the theme for the current Admin Form
            $this->get('twig')->getExtension('form')->renderer->setTheme($view, $this->admin->getFormTheme());

            $pageSettingsTemplate = $this->render(
                $this->admin->getTemplate('edit'),
                array(
                    'action' => 'edit',
                    'form' => $view,
                    'object' => $object,
                )
            );

            return $this->renderJson(
                array(
                    'result' => 'ok',
                    'objectId' => $this->admin->getNormalizedIdentifier($object),
                    'title' => $object->__toString(),
                    'pageStatus' => $this->admin->trans($object->getStatus()),
                    'pageSettings' => $pageSettingsTemplate
                )
            );
        }

        $this->get('session')->getFlashBag()->add('sonata_flash_success', $this->admin->trans('flash_status_success'));

        return $this->redirect($this->admin->generateObjectUrl('edit', $object));
    }

    /**
     * @param Request $request
     * @param null $id
     * @return RedirectResponse|Response
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


            $pageManager = $this->get('networking_init_cms.page_manager');
            $serializer = $this->get('serializer');

            $publishedPage = $pageManager->revertToPublished($draftPage, $serializer);

            if ($request->isXmlHttpRequest()) {
                $form = $this->admin->getForm();
                $form->setData($publishedPage);

                $pageSettingsTemplate = $this->render(
                    $this->admin->getTemplate('edit'),
                    array(
                        'action' => 'edit',
                        'form' => $form->createView(),
                        'object' => $publishedPage,
                    )
                );

                return $this->renderJson(
                    array(
                        'result' => 'ok',
                        'objectId' => $this->admin->getNormalizedIdentifier($publishedPage),
                        'title' => $publishedPage->__toString(),
                        'pageStatus' => $this->admin->trans($publishedPage->getStatus()),
                        'pageSettings' => $pageSettingsTemplate->getContent()
                    )
                );
            }


            return $this->redirect($this->admin->generateObjectUrl('edit', $publishedPage));

        }

        return $this->render(
            'NetworkingInitCmsBundle:PageAdmin:page_cancel_draft.html.twig',
            array(
                'action' => 'cancelDraft',
                'page' => $draftPage,
                'admin' => $this->admin
            )
        );
    }

    /**
     * @param Request $request
     * @param null $id
     * @return RedirectResponse|Response
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
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

        if ($this->isXmlHttpRequest()) {

            $view = $form->createView();

            // set the theme for the current Admin Form
            $this->get('twig')->getExtension('form')->renderer->setTheme($view, $this->admin->getFormTheme());

            $pageSettingsTemplate = $this->render(
                $this->admin->getTemplate('edit'),
                array(
                    'action' => 'edit',
                    'form' => $view,
                    'object' => $object,
                )
            );

            return $this->renderJson(
                array(
                    'result' => 'ok',
                    'objectId' => $this->admin->getNormalizedIdentifier($object),
                    'title' => $object->__toString(),
                    'pageStatus' => $this->admin->trans($object->getStatus()),
                    'pageSettings' => $pageSettingsTemplate
                )
            );
        }

        $request->getSession()->getFlashBag()->add(
            'sonata_flash_success',
            $this->admin->trans('flash_publish_success')
        );

        return $this->redirect($this->admin->generateObjectUrl('edit', $object));
    }


    /**
     * Create a snapshot of a published page
     *
     * @param PageInterface $page
     */
    protected function makeSnapshot(PageInterface $page)
    {
        if (!$this->admin->isGranted('PUBLISH', $page)) {
            return;
        }

        /** @var $pageHelper \Networking\InitCmsBundle\Helper\PageHelper */
        $pageHelper = $this->get('networking_init_cms.helper.page_helper');

        $pageHelper->makePageSnapshot($page);
    }

    /**
     * Return a json array with the calculated path for a page object
     *
     * @param Request $request
     * @internal param string $path
     * @return Response
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

        return $this->renderJson(array('path' => $path . $getPath));
    }
}
