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

use Networking\InitCmsBundle\Admin\LayoutBlockAdmin;
use Networking\InitCmsBundle\Admin\PageAdmin;
use Networking\InitCmsBundle\Cache\PageCacheInterface;
use Networking\InitCmsBundle\Component\EventDispatcher\CmsEventDispatcher;
use Networking\InitCmsBundle\Entity\LayoutBlock;
use Networking\InitCmsBundle\Model\PageManagerInterface;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\AdminBundle\Form\FormErrorIteratorToConstraintViolationList;
use Symfony\Component\Form\FormError;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class LayoutBlockController.
 *
 * @author net working AG <info@networking.ch>
 */
class LayoutBlockController extends CRUDController
{

    /**
     * @var bool
     */
    protected $error = false;

    /**
     * @var PageManagerInterface
     */
    protected $pageManager;

    /**
     * LayoutBlockController constructor.
     * @param CmsEventDispatcher $dispatcher
     * @param PageCacheInterface $pageCache
     * @param PageManagerInterface $pageManager
     */
    public function __construct(
        CmsEventDispatcher $dispatcher,
        PageCacheInterface $pageCache,
        PageManagerInterface $pageManager
    ) {
        $this->pageManager = $pageManager;

        parent::__construct($dispatcher, $pageCache);
    }


    /**
     * @return Response
     * @throws \Twig\Error\RuntimeError
     * @throws \Twig\Error\RuntimeError
     */
    public function createAction(Request $request): Response
    {
        if (!$this->isXmlHttpRequest($request)) {
            return new Response('cannot load external of page module', 403);
        }

        if (false === $this->admin->isGranted('CREATE')) {
            throw new AccessDeniedException();
        }

        $layoutBlock = $this->admin->getNewInstance();
        /** @var Request $request */
        $this->admin->setSubject($layoutBlock);


        $code = $request->get('code');
        $pageId = $request->get('pageId');


        $page = $this->pageManager->find($pageId);
        if ($pageId && !$page) {
            throw new NotFoundHttpException();
        }

        if (!$page) {
            throw new NotFoundHttpException();
        }


        if($request->get('zone')){
            $layoutBlock->setZone($request->get('zone'));
        }
        if($request->get('sortOrder')){
            $layoutBlock->setSortOrder($request->get('sortOrder'));
        }
        $layoutBlock->setPage($page);

        if ($request->isMethod('POST')) {
            try {
                // persist if the form was valid and if in preview mode the preview was approved
                if ((!$this->isInPreviewMode($request) || $this->isPreviewApproved($request))) {
                    $this->admin->create($layoutBlock);
                    if ($this->isXmlHttpRequest($request)) {
                        return $this->renderJson(
                            [
                                'result' => 'ok',
                                'messageStatus' => 'success',
                                'message' => $this->translate('message.layout_block_created'),
                                'layoutBlockId' => $this->admin->getNormalizedIdentifier($layoutBlock),
                                'zone' => $layoutBlock->getZone(),
                                'sortOder' => $layoutBlock->getSortOrder(),
                                'html' => $this->renderView('@NetworkingInitCms/PageAdmin/layout_block.html.twig', ['layout_block' => $layoutBlock])
                            ]
                        );
                    }
                }

            } catch (ModelManagerException $e) {
                return $this->json(
                    ['status' => 'error', 'message' => $e->getMessage()],
                    Response::HTTP_BAD_REQUEST
                );
            }
        }



    }

    /**
     * @param null $id
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
     * @throws \Twig\Error\RuntimeError
     */
    public function editAction(Request $request, $id = null): Response
    {

        $pageId = $request->get('pageId');
        $formFieldId = $request->get('formFieldId');
        $uniqId = $request->get('uniqId');
        $code = $request->get('code', 'networking_init_cms.admin.page');


        $layoutBlock = $this->admin->getObject($id);


        /** @var $form \Symfony\Component\Form\Form */
        $form = $this->admin->getForm();

        $form->setData($layoutBlock);

        $form->handleRequest($request);
        $status = 200;

        if ($form->isSubmitted()) {
            if ($form->isValid()) {
                $this->admin->update($layoutBlock);

                $adminContent = $layoutBlock->getAdminContent();
                $html = $this->renderView($adminContent['template'], $adminContent['content']);


                return new JsonResponse(
                    [
                        'id' => $this->admin->getNormalizedIdentifier($layoutBlock),
                        'status' => 'success',
                        'message' => $this->translate('message.layout_block_updated'),
                        'html' => $html,
                    ],
                    $status
                );
            } else {
                $this->error = true;

                $status = 400;
            }

        }

        $view = $form->createView();

        // set the theme for the current Admin Form
        $this->container->get('twig')->getRuntime(FormRenderer::class)->setTheme($view, $this->admin->getFormTheme());

        $html= $this->renderView(
            '@NetworkingInitCms/PageAdmin/layout_block_edit.html.twig',
            $this->addRenderExtraParams( [
                'action' => 'create',
                'form' => $view,
                'object' => $layoutBlock,
                'ad'
            ])

        );

        return new JsonResponse(
            [
                'status' => $status === 200? 'success':'error',
                'message' => $this->translate('message.layout_block_error'),
                'html' => $html,
                'id' => $this->admin->getNormalizedIdentifier($layoutBlock),
            ],
            $status
        );
    }

    /**
     *
     * @return Response
     * @throws \Twig\Error\RuntimeError
     */
    public function reloadAction(Request $request)
    {
        $pageId = $request->get('pageId');
        $formFieldId = $request->get('formFieldId');
        $uniqId = $request->get('uniqId');
        $code = $request->get('code', 'networking_init_cms.admin.page');
        $html = $this->getLayoutBlockFormWidget($request, $pageId, $formFieldId, $uniqId, $code);
        $status = 200;

        return new Response($html, $status);
    }

    /**
     * @param $pageId
     * @param $formFieldId
     * @param null $uniqId
     * @param string $code
     *
     * @return mixed
     *
     * @throws \Twig\Error\RuntimeError
     */
    protected function getLayoutBlockFormWidget(
        Request $request,
        $pageId,
        $formFieldId,
        $uniqId,
        $code = 'networking_init_cms.admin.page'
    ) {
        if (!$formFieldId || !$code) {
            throw new NotFoundHttpException();
        }

        /** @var \Networking\InitCmsBundle\Admin\PageAdmin $pageAdmin */
        $pageAdmin = $this->container->get($code);

        $pageAdmin->setRequest($request);
        $pageAdmin->setUniqid($uniqId);

        $page = $pageAdmin->getModelManager()->find($pageAdmin->getClass(), $pageId);
        if ($pageId && !$page) {
            throw new NotFoundHttpException();
        }

        if (!$page) {
            $page = $pageAdmin->getNewInstance();
        }

        $request->attributes->add(['objectId' => $pageId]);
        $request->attributes->add(['page_locale' => $page->getLocale()]);

        $pageAdmin->setSubject($page);
        $formBuilder = $pageAdmin->getFormBuilder();

        /** @var \Symfony\Component\Form\Form $form */
        $form = $formBuilder->getForm();
        $form->setData($page);
        $view = $form->get('layoutBlock')->createView();
        $id = $form->get('layoutBlock')->getViewData()['id'];
        $twig = $this->container->get('twig');
        $twig->getRuntime(FormRenderer::class)->setTheme($view, $this->admin->getFormTheme());

        return $this->renderView(
            '@NetworkingInitCms/PageAdmin/page_edit_layout_blocks.html.twig',
            [
                'admin' => $pageAdmin,
                'form' => $view,
                'id' => $view->vars['id']
            ]
        );
    }

    /**
     * @return JsonResponse
     */
    public function updateLayoutBlockSortAction(Request $request)
    {
        $zones = $request->get('zones', []);
        $pageId = $request->get('pageId');
        $code = $request->get('code');

        if (!$pageId) {
            throw $this->createNotFoundException();
        }
        $pageAdmin = $this->container->get($code);
        foreach ($zones as $zone) {
            $zoneName = $zone['zone'];
            if (array_key_exists('layoutBlocks', $zone) && is_array($zone['layoutBlocks'])) {
                foreach ($zone['layoutBlocks'] as $key => $layoutBlockStr) {
                    $sort = ++$key;
                    $blockId = str_replace('layoutBlock_', '', (string) $layoutBlockStr);

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

                            return new JsonResponse(['messageStatus' => 'error', 'message' => $message]);
                        }
                    }
                }
            }
        }


        $page = $pageAdmin->getModelManager()->find($pageAdmin->getClass(), $pageId);
        $pageAdmin->setSubject($page);

        $pageStatus = $this->renderView(
            '@NetworkingInitCms/PageAdmin/page_status_settings.html.twig',
            ['admin' => $pageAdmin, 'object' => $page]
        );

        $data = [
            'pageStatusSettings' => $pageStatus,
            'pageStatus' => $this->translate($page->getStatus()),
            'messageStatus' => 'success',
            'message' => $this->translate('message.layout_blocks_sorted', ['zone' => '']),
        ];

        return new JsonResponse($data);
    }

    /**
     *
     * @return JsonResponse
     * @throws \Twig\Error\RuntimeError
     */
    public function deleteAjaxAction(Request $request)
    {
        $id = $request->get('id');

        if ($id) {
            $layoutBlock = $this->admin->getObject($id);
            if ($layoutBlock) {
                $this->admin->delete($layoutBlock);
            }
        }

        return new JsonResponse(
            [
                'messageStatus' => 'success',
                'message' => $this->translate('message.layout_block_deleted'),
            ]
        );
    }

    /**
     *
     * @return JsonResponse
     * @throws \Twig\Error\RuntimeError
     */
    public function toggleActiveAction(Request $request)
    {
        $layoutBlock = null;
        $id = $request->get('id');
        $pageId = $request->get('pageId');
        $uniqId = $request->get('uniqId');
        $code = $request->get('code');
        $formFieldId = $request->get('formFieldId');

        if ($id) {
            /** @var LayoutBlock $layoutBlock */
            $layoutBlock = $this->admin->getObject($id);
            $layoutBlock->setIsActive(!$layoutBlock->getIsActive());
            $this->admin->update($layoutBlock);
        }
        $status = $layoutBlock->getIsActive()?'activated':'deactivated';
        return new JsonResponse(
            [
                'active' => $layoutBlock->getIsActive(),
                'messageStatus' => 'success',
                'message' => $this->translate(sprintf('message.layout_block_%s', $status)),
            ]
        );
    }

    public static function getSubscribedServices(): array
    {
        return [
                'networking_init_cms.admin.page' => \Networking\InitCmsBundle\Admin\PageAdmin::class,
            ] + parent::getSubscribedServices();
    }
}
