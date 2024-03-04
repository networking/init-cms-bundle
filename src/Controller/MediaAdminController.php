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
use Doctrine\Persistence\ManagerRegistry;
use Networking\InitCmsBundle\Admin\TagAdmin;
use Networking\InitCmsBundle\Entity\Media;
use Networking\InitCmsBundle\Entity\Tag;
use Networking\InitCmsBundle\Exception\DuplicateMediaException;
use Networking\InitCmsBundle\Lib\UploadedBase64File;
use Networking\InitCmsBundle\Provider\ImageProvider;
use Oneup\UploaderBundle\Uploader\File\FileInterface;
use Oneup\UploaderBundle\Uploader\File\FilesystemFile;
use Oneup\UploaderBundle\Uploader\Response\FineUploaderResponse;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\MediaBundle\Provider\FileProvider;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Form\FormInterface;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\Validator\Validator\ValidatorInterface;

/**
 * Class MediaAdminController.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class MediaAdminController extends CRUDController
{
    public function showAction(Request $request): Response
    {
        $media = $this->assertObjectExists($request, true);
        \assert(null !== $media);

        $this->admin->checkAccess('show', $media);

        if (!$media) {
            throw new NotFoundHttpException('unable to find the media with the id');
        }

        return $this->renderWithExtraParams(
            '@NetworkingInitCms/MediaAdmin/show.html.twig',
            [
                'media' => $media,
                'formats' => $this->container->get('sonata.media.pool')->getFormatNamesByContext($media->getContext()),
                'format' => $request->get('format', 'reference'),
                'base_template' => $this->getBaseTemplate(),
                'admin' => $this->admin,
                'security' => $this->container->get('sonata.media.pool')->getDownloadStrategy($media),
                'action' => 'view',
                'pixlr' => $this->container->has('sonata.media.extra.pixlr') ? $this->container->get(
                    'sonata.media.extra.pixlr'
                ) : false,
            ]
        );
    }

    /**
     * @param Request|null $request
     */
    public function createAction(Request $request): Response
    {
        $this->admin->checkAccess('create');

        $parameters = $this->admin->getPersistentParameters();

        if (!array_key_exists('provider', $parameters) || !$parameters['provider']) {
            return $this->render(
                '@NetworkingInitCms/MediaAdmin/select_provider.html.twig',
                [
                    'providers' => $this->container->get('sonata.media.pool')->getProvidersByContext(
                        $request->get('context', $this->container->get('sonata.media.pool')->getDefaultContext())
                    ),
                    'base_template' => $this->getBaseTemplate(),
                    'admin' => $this->admin,
                    'action' => 'create',
                ]
            );
        }

        if (!$this->admin->hasSubject() && !$request->query->get('gallery') && !$request->query->get('pcode')) {
            $provider = $this->container->get('sonata.media.pool')->getProvider(
                $parameters['provider']
            );

            if ($provider instanceof FileProvider) {
                $newObject = $this->admin->getNewInstance();

                $preResponse = $this->preCreate($request, $newObject);
                if (null !== $preResponse) {
                    return $preResponse;
                }

                $this->admin->setSubject($newObject);

                $form = $this->admin->getForm();

                $form->setData($newObject);
                $form->handleRequest($request);
                $formView = $form->createView();
                $this->setFormTheme($formView, $this->admin->getFormTheme());
                $template = $this->admin->getTemplateRegistry()->getTemplate('create');

                return $this->renderWithExtraParams($template, [
                    'action' => 'create',
                    'form' => $formView,
                    'object' => $newObject,
                    'objectId' => null,
                ]);
            }
        }

        return parent::createAction($request);
    }

    protected function handleXmlHttpRequestErrorResponse(Request $request, FormInterface $form): ?JsonResponse
    {
        return null;
    }

    /**
     * redirect the user depend on this choice.
     *
     * @return Response
     */
    public function redirectTo(Request $request, object $object): RedirectResponse
    {
        $url = false;

        if ($request->get('btn_update_and_list')) {
            $url = $this->admin->generateUrl('list', ['active_tab' => $request->get('context')]);
        }
        if ($request->get('btn_create_and_list')) {
            $url = $this->admin->generateUrl('list', ['active_tab' => $request->get('context')]);
        }

        if ($request->get('btn_create_and_create')) {
            $params = [];
            if ($this->admin->hasActiveSubClass()) {
                $params['subclass'] = $request->get('subclass');
            }
            $url = $this->admin->generateUrl('create', $params);
        }

        if (!$url) {
            $url = $this->admin->generateObjectUrl('edit', $object);
        }

        return new RedirectResponse($url);
    }

    /**
     * @return \Symfony\Bundle\FrameworkBundle\Controller\Response|\Symfony\Component\HttpFoundation\Response|RedirectResponse
     *
     * @throws NotFoundHttpException
     * @throws AccessDeniedException
     */
    public function deleteAction(Request $request): Response
    {
        $id = null;
        $object = $this->assertObjectExists($request);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('DELETE', $object)) {
            throw new AccessDeniedException();
        }
        /** @var Request $request */
        $request = $this->container->get('request_stack')->getCurrentRequest();
        if ('DELETE' == $request->getMethod()) {
            try {
                $name = $object->__toString();
                $this->admin->delete($object);
                $this->addFlash('sonata_flash_success', $this->trans('flash_delete_success', ['%name%' => $name], 'SonataAdminBundle'));
            } catch (ModelManagerException) {
                $this->addFlash('sonata_flash_error', $this->trans('flash_delete_error', [], 'SonataAdminBundle'));
            }

            return new RedirectResponse($this->admin->generateUrl(
                'list',
                ['active_tab' => $request->get('context')]
            ));
        }

        return $this->renderWithExtraParams(
            $this->admin->getTemplateRegistry()->getTemplate('delete'),
            [
                'object' => $object,
                'action' => 'delete',
            ]
        );
    }

    public function batchActionAddTags(ProxyQueryInterface $selectedModelQuery)
    {
        $tagAdmin = $this->container->get('networking_init_cms.admin.tag');
        if (!$this->admin->isGranted('EDIT') || !$this->admin->isGranted('DELETE')) {
            throw new AccessDeniedException();
        }

        $modelManager = $tagAdmin->getModelManager();

        /** @var Tag $tag */
        $tag = $modelManager->find($tagAdmin->getClass(), $this->container->get('request_stack')->getCurrentRequest()->get('tags'));

        $data = [
            'result' => 'ok',
            'status' => 'warning',
            'message' => $this->trans('tag_not_selected'),
        ];

        if (null !== $tag) {
            $selectedModels = $selectedModelQuery->execute();

            $status = 'success';
            $message = 'tag_added';

            try {
                /** @var Media $selectedModel */
                foreach ($selectedModels as $selectedModel) {
                    if ($selectedModel->getTags()->contains($tag)) {
                        $status = 'success';
                        $message = 'tag_added';
                        continue;
                    }
                    if (!$this->getParameter('networking_init_cms.multiple_media_tags')) {
                        $selectedModel->setTags(new ArrayCollection());
                    }
                    $selectedModel->addTags($tag);
                    $this->admin->getModelManager()->update($selectedModel);
                }
            } catch (\Exception $e) {
                $status = 'error';
                $message = $e->getMessage();
            }

            $data = [
                'result' => 'ok',
                'status' => $status,
                'tag' => $tag->getId(),
                'media' => $selectedModel->getId(),
                'message' => $this->trans($message, ['%tag%' => $tag->getPath()]), ];
        }

        return $this->renderJson($data);
    }

    public function gallery(Request $request)
    {
        $request->query->set('galleryMode', true);

        return $this->listAction($request);
    }

    /**
     * This method can be overloaded in your custom CRUD controller.
     * It's called from listAction.
     */
    protected function preList(Request $request): ?Response
    {
        $filter = $this->admin->getFilterParameters();

        if ($request->query->has('provider')) {
            $filter['providerName']['value'] = $request->query->get('provider');
        }

        if ($request->query->has('context')) {
            $filter['context']['value'] = $request->query->get('context');
        }
        $request->query->set('filter', $filter);

        return null;
    }

    /**
     * @param Request|null $request
     */
    public function listAction(Request $request): Response
    {
        $this->admin->checkAccess('list');

        $preResponse = $this->preList($request);
        if (null !== $preResponse) {
            return $preResponse;
        }

        $galleryMode = $request->query->get('galleryMode', false);
        $multiSelect = $request->query->get('multiSelect', false);

        if ($request->query->has('pcode')) {
            $galleryMode = true;
        }

        $datagrid = $this->admin->getDatagrid();
        $formView = $datagrid->getForm()->createView();

        $persistentParameters = $this->admin->getPersistentParameters();

        $filterPersister = $this->admin->getFilterPersister();

        $filters = $filterPersister->get($this->admin->getCode());
        $this->setFormTheme($formView, $this->admin->getFilterTheme());

        $tags = $this->container->get('doctrine')
            ->getRepository(Tag::class)
            ->createQueryBuilder('t')
            ->select('t', 'c')
            ->leftJoin('t.children', 'c') // preload
            ->orderBy('t.path', 'ASC')
            ->getQuery()->getResult();

        $tagAdmin = $this->container->get('networking_init_cms.admin.tag');
        $context = $request->query->get('context', $persistentParameters['context']);
        if (!$context) {
            $context = $persistentParameters['context'];
        }

        $tagFilter = $datagrid->getFilter('tags');
        $selectedTag = false;

        if (array_key_exists('tags', $filters) && array_key_exists('value', $filters['tags'])) {
            $selectedTag = (int) $filters['tags']['value'];
        }
        $mediaPool = $this->container->get('sonata.media.pool');

        $selected = $request->query->get('selected', false);

        if ($selected) {
            $selected = explode(',', $selected);
        }

        $params = $this->addRenderExtraParams([
            'multiSelect' => $multiSelect,
            'selected' => $multiSelect ? $selected : [],
            'providers' => $mediaPool->getProvidersByContext($context),
            'media_pool' => $mediaPool,
            'persistent_parameters' => $persistentParameters,
            'currentProvider' => array_key_exists('providerName', $filters) ? $filters['providerName']['value'] : $persistentParameters['provider'],
            'tags' => $tags,
            'tagAdmin' => $tagAdmin,
            'tagJson' => $tagAdmin->getTagTree($selectedTag),
            'lastItem' => 0,
            'action' => 'list',
            'form' => $formView,
            'datagrid' => $datagrid,
            'galleryListMode' => $galleryMode,
            'csrf_token' => $this->getCsrfToken('sonata.batch'),
            'show_actions' => true,
        ]);

        return $this->render(
            '@NetworkingInitCms/MediaAdmin/list.html.twig',
            $params
        );
    }

    /**
     * @return Response
     */
    public function refreshList(Request $request)
    {
        $this->admin->checkAccess('list');

        $preResponse = $this->preList($request);
        if (null !== $preResponse) {
            return $preResponse;
        }

        $galleryMode = $request->query->get('galleryMode', false);
        $multiSelect = $request->query->get('multiSelect', false);
        $datagrid = $this->admin->getDatagrid();
        $datagrid->getForm()->createView();
        $persistentParameters = $this->admin->getPersistentParameters();

        $tags = $this->container->get('doctrine')
            ->getRepository(Tag::class)
            ->createQueryBuilder('t')
            ->select('t', 'c')
            ->leftJoin('t.children', 'c') // preload
            ->orderBy('t.path', 'ASC')
            ->getQuery()->getResult();

        $tagAdmin = $this->container->get('networking_init_cms.admin.tag');

        $selected = $request->query->get('selected', false);

        if ($selected) {
            $selected = explode(',', $selected);
        }

        return $this->renderWithExtraParams(
            '@NetworkingInitCms/MediaAdmin/list_items.html.twig',
            [
                'providers' => $this->container->get('sonata.media.pool')->getProvidersByContext(
                    $request->get('context', $persistentParameters['context'])
                ),
                'multiSelect' => $multiSelect,
                'selected' => $multiSelect ? $selected : [],
                'media_pool' => $this->container->get('sonata.media.pool'),
                'persistent_parameters' => $this->admin->getPersistentParameters(),
                'tags' => $tags,
                'tagAdmin' => $tagAdmin,
                'lastItem' => 0,
                'action' => 'list',
                'datagrid' => $datagrid,
                'galleryListMode' => $galleryMode,
                'show_actions' => true,
            ]
        );
    }

    /**
     * @return Response
     */
    public function previewPdf($id)
    {
        $object = $this->admin->getObject($id);

        $response = new Response();
        $response->headers->set('X-Frame-Options', 'Sameorigin');

        return $this->render('@NetworkingInitCms/MediaAdmin/preview.html.twig', [
            'media_pool' => $this->container->get('sonata.media.pool'),
            'persistent_parameters' => $this->admin->getPersistentParameters(),
            'admin' => $this->admin,
            'object' => $object],
            $response
        );
    }

    public function clone(Request $request)
    {
        $response = new FineUploaderResponse();

        try {
            $this->handleUpload($response, $request);
        } catch (UploadException $e) {
            $response->setSuccess(false);
            $response->setError($e->getMessage());
            if ($e instanceof DuplicateMediaException) {
                $response->offsetSet('duplicate', true);
            }
            $response['error'] = $e->getMessage();
        }

        $response = new JsonResponse($response->assemble(), 200);
        $response->headers->set('Vary', 'Accept');

        if (!in_array('application/json', $request->getAcceptableContentTypes(), true)) {
            $response->headers->set('Content-type', 'text/plain');
        }

        return $response;
    }

    public function extractBase64String(string $base64Content)
    {
        $data = explode(';base64,', $base64Content);

        return $data[1];
    }

    private function fixExtension($filename, $newExtension): string
    {
        $info = pathinfo((string) $filename);

        return $info['filename'].'.'.$newExtension;
    }

    public function handleUpload($response, Request $request)
    {
        $request->query->set('oneuploader', true);

        $content = $request->getContent();
        $content = json_decode($content, null, 512, JSON_THROW_ON_ERROR);
        $fileData = $content->file;
        $clone = $content->clone;
        $id = $content->id;

        $this->admin->setRequest($request);

        /** @var Media $baseMedia */
        $baseMedia = $this->admin->getObject($id);

        /** @var ImageProvider $provider */
        $provider = $this->container->get('sonata.media.pool')->getProvider($baseMedia->getProviderName());

        $image = $provider->getReferenceImage($baseMedia);

        $fileName = $this->fixExtension($image, 'png');

        if ($clone) {
            /** @var Media $media */
            $media = $this->admin->getNewInstance();
            $media->setEnabled(true);
            $media->setName('Copy_'.$baseMedia->getName());
            foreach ($baseMedia->getTags() as $tag) {
                $media->addTags($tag);
            }
            $media->setProviderName($baseMedia->getProviderName());
            $media->setContext($baseMedia->getContext());
        } else {
            $media = $baseMedia;
        }

        $data = $this->extractBase64String($fileData);

        $file = new UploadedBase64File($data, $fileName, 'image/png');

        if (!($file instanceof FileInterface)) {
            $file = new FilesystemFile($file);
        }

        $media->setBinaryContent($file);

        $provider->transform($media);

        $validator = $this->container->get('validator.validator');

        $errors = $validator->validate($media);

        $errorMessages = [];
        if ($errors->count() > 0) {
            $duplicate = false;
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();

                if ('File is duplicate' == $error->getMessage()) {
                    $duplicate = true;
                }
            }

            if ($duplicate) {
                $originalMedia = $this->admin->checkForDuplicate($media);
                $path = $this->admin->generateObjectUrl('edit', $originalMedia);
                $response->offsetSet('url', $path);
                $response->offsetSet('id', $originalMedia->getId());

                throw new DuplicateMediaException('File is duplicate');
            }

            throw new UploadException(implode(', ', $errorMessages));
        }

        try {
            if ($clone) {
                $this->admin->create($media);
            } else {
                $this->admin->update($media);
            }
            $path = $this->admin->generateObjectUrl('edit', $media);
            $response->offsetSet('url', $path);
            $response->offsetSet('id', $media->getId());
        } catch (\Exception $e) {
            throw new UploadException($e->getMessage());
        }
    }

    public function gallerySelect(Request $request)
    {
        $request->query->set('galleryMode', true);
        $request->query->set('multiSelect', true);

        return $this->listAction($request);
    }

    /**
     * @return Response
     */
    public function refreshSelectList(Request $request)
    {
        $request->query->set('galleryMode', true);
        $request->query->set('multiSelect', true);

        return $this->refreshList($request);
    }

    public static function getSubscribedServices(): array
    {
        return [
                'doctrine' => ManagerRegistry::class,
                'validator.validator' => ValidatorInterface::class,
                'networking_init_cms.admin.tag' => TagAdmin::class,
                'sonata.media.pool' => Pool::class,
                'sonata.media.manager.category' => '?'.CategoryManagerInterface::class,
                'sonata.media.manager.context' => '?'.ContextManagerInterface::class,
            ] + parent::getSubscribedServices();
    }
}
