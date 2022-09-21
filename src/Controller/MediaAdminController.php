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
use Doctrine\DBAL\DBALException;
use Networking\InitCmsBundle\Admin\Model\TagAdmin;
use Networking\InitCmsBundle\Entity\Media;
use Networking\InitCmsBundle\Entity\Tag;
use Networking\InitCmsBundle\Exception\DuplicateMediaException;
use Networking\InitCmsBundle\Lib\UploadedBase64File;
use Networking\InitCmsBundle\Provider\ImageProvider;
use Oneup\UploaderBundle\Uploader\ErrorHandler\ErrorHandlerInterface;
use Oneup\UploaderBundle\Uploader\File\FileInterface;
use Oneup\UploaderBundle\Uploader\File\FilesystemFile;
use Oneup\UploaderBundle\Uploader\Response\FineUploaderResponse;
use Sonata\AdminBundle\Datagrid\ProxyQueryInterface;
use Sonata\AdminBundle\Exception\ModelManagerException;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Sonata\MediaBundle\Controller\MediaAdminController as SonataMediaAdminController;
use Sonata\MediaBundle\Provider\FileProvider;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sonata\AdminBundle\Controller\CRUDController as Controller;
/**
 * Class MediaAdminController.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class MediaAdminController extends CRUDController
{

    /**
     * @param null $id
     *
     * @return Response
     */
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
     *
     * @return Response
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

    /**
     * redirect the user depend on this choice.
     *
     * @param object $object
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
     * @param mixed $id
     *
     * @throws NotFoundHttpException
     * @throws AccessDeniedException
     *
     * @return \Symfony\Bundle\FrameworkBundle\Controller\Response|\Symfony\Component\HttpFoundation\Response|RedirectResponse
     */
    public function deleteAction(Request $request): Response
    {
        $object = $this->assertObjectExists($request);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('DELETE', $object)) {
            throw new AccessDeniedException();
        }
        /** @var Request $request */
        $request = $this->container->get('request_stack')->getCurrentRequest();
        if ($request->getMethod() == 'DELETE') {
            try {
                $this->admin->delete($object);
                $this->addFlash('sonata_flash_success', $this->trans('flash_delete_success', [], 'SonataAdminBundle'));
            } catch (ModelManagerException $e) {
                $this->addFlash('sonata_flash_error', $this->trans('flash_delete_error', [], 'SonataAdminBundle'));
            }

            return new RedirectResponse($this->admin->generateUrl(
                'list',
                ['active_tab' => $request->get('context')]
            ));
        }

        return $this->render(
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
            'message' => $this->admin->trans('tag_not_selected'),
        ];

        if ($tag !== null) {
            $selectedModels = $selectedModelQuery->execute();

            try {
                /** @var Media $selectedModel */
                foreach ($selectedModels as $selectedModel) {
                    if (!$this->getParameter('networking_init_cms.multiple_media_tags')) {
                        $selectedModel->setTags(new ArrayCollection());
                    }
                    $selectedModel->addTags($tag);
                    $this->admin->getModelManager()->update($selectedModel);
                }

                $status = 'success';
                $message = 'tag_added';
            } catch (\Exception $e) {
                $status = 'error';
                $message = 'tag_not_added';
            }

            $data = [
                'result' => 'ok',
                'status' => $status,
                'message' => $this->admin->trans($message, ['%tag%' => $tag->getPath()]), ];
        }

        return $this->renderJson($data);
    }

    public function galleryAction(Request $request){
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

        if($request->query->has('provider')){
            $filter['providerName']['value'] = $request->query->get('provider');
        }

        if($request->query->has('context')){
            $filter['context']['value'] = $request->query->get('context');
        }
        $request->query->set('filter', $filter);

        return null;
    }


    /**
     * @param Request|null $request
     *
     * @return Response
     */
    public function listAction(Request $request): Response
    {
        $this->admin->checkAccess('list');

        $preResponse = $this->preList($request);
        if (null !== $preResponse) {
            return $preResponse;
        }

        $galleryMode = $request->query->get('galleryMode', false);

        if($request->query->has('pcode')){
            $galleryMode = true;
        }

        $datagrid = $this->admin->getDatagrid();
        $formView = $datagrid->getForm()->createView();

        $persistentParameters = $this->admin->getPersistentParameters();
        $this->setFormTheme($formView, $this->admin->getFilterTheme());

        $tags = $this->getDoctrine()
            ->getRepository(Tag::class)
            ->createQueryBuilder('t')
            ->select('t', 'c')
            ->leftJoin('t.children', 'c') // preload
            ->orderBy('t.path', 'ASC')
            ->getQuery()->getResult();

        $tagAdmin = $this->container->get('networking_init_cms.admin.tag');


        return $this->renderWithExtraParams(
            '@NetworkingInitCms/MediaAdmin/list.html.twig',
            [
                'providers' => $this->container->get('sonata.media.pool')->getProvidersByContext(
                    $request->get('context', $persistentParameters['context'])
                ),
                'media_pool' => $this->container->get('sonata.media.pool'),
                'persistent_parameters' => $this->admin->getPersistentParameters(),
                'tags' => $tags,
                'tagAdmin' => $tagAdmin,
                'lastItem' => 0,
                'action' => 'list',
                'form' => $formView,
                'datagrid' => $datagrid,
                'galleryListMode' => $galleryMode,
                'csrf_token' => $this->getCsrfToken('sonata.batch'),
                'show_actions' => true,
            ]
        );
    }

    /**
     * @param Request $request
     *
     * @return Response
     */
    public function refreshListAction(Request $request)
    {
        $this->admin->checkAccess('list');

        $preResponse = $this->preList($request);
        if (null !== $preResponse) {
            return $preResponse;
        }
        
        $galleryListMode = $request->query->get('galleryMode', false);
        $datagrid = $this->admin->getDatagrid();
        $datagrid->getForm()->createView();
        $persistentParameters = $this->admin->getPersistentParameters();

        $tags = $this->getDoctrine()
            ->getRepository(Tag::class)
            ->createQueryBuilder('t')
            ->select('t', 'c')
            ->leftJoin('t.children', 'c') // preload
            ->orderBy('t.path', 'ASC')
            ->getQuery()->getResult();

        $tagAdmin = $this->container->get('networking_init_cms.admin.tag');

        return $this->renderWithExtraParams(
            '@NetworkingInitCms/MediaAdmin/list_items.html.twig',
            [
                'providers' => $this->container->get('sonata.media.pool')->getProvidersByContext(
                    $request->get('context', $persistentParameters['context'])
                ),
                'media_pool' => $this->container->get('sonata.media.pool'),
                'persistent_parameters' => $this->admin->getPersistentParameters(),
                'tags' => $tags,
                'tagAdmin' => $tagAdmin,
                'lastItem' => 0,
                'action' => 'list',
                'datagrid' => $datagrid,
                'galleryListMode' => $galleryListMode,
                'show_actions' => true,
            ]
        );
    }

    /**
     * @param $id
     *
     * @return Response
     */
    public function previewPdfAction($id)
    {
        $object = $this->admin->getObject($id);

        return $this->renderWithExtraParams('@NetworkingInitCms/MediaAdmin/preview.html.twig', [

            'media_pool' => $this->container->get('sonata.media.pool'),
            'persistent_parameters' => $this->admin->getPersistentParameters(),
            'object' => $object]);
    }

    public function cloneAction(Request $request){

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

        $request = $request;
        $response = new JsonResponse($response->assemble(), 200);
        $response->headers->set('Vary', 'Accept');

        if (!in_array('application/json', $request->getAcceptableContentTypes(), true)) {
            $response->headers->set('Content-type', 'text/plain');
        }

        return $response;
    }

    /**
     * @param string $base64Content
     * @return mixed
     */
    public function extractBase64String(string $base64Content)
    {
        $data = explode( ';base64,', $base64Content);
        return $data[1];

    }

    /**
     * @param $filename
     * @param $newExtension
     * @return string
     */
    private function fixExtension($filename, $newExtension) {
        $info = pathinfo($filename);
        return $info['filename'] . '.' . $newExtension;
    }

    public function handleUpload($response, Request $request){
        $request->query->set('oneuploader', true);

        $content = $request->getContent();
        $content = json_decode($content);
        $fileData = $content->file;
        $clone = $content->clone;
        $id = $content->id;

        $this->admin->setRequest($request);

        /** @var Media $baseMedia */
        $baseMedia = $this->admin->getObject($id);


        /** @var ImageProvider $provider */
        $provider = $this->admin->getPool()->getProvider($baseMedia->getProviderName());


        $image = $provider->getReferenceImage($baseMedia);

        $fileName = $this->fixExtension($image, 'png');

        if($clone){
            /** @var Media $media */
            $media = $this->admin->getNewInstance();
            $media->setEnabled(true);
            $media->setName('Copy_'.$baseMedia->getName());
            foreach ($baseMedia->getTags() as $tag){
                $media->addTags($tag);
            }
            $media->setProviderName($baseMedia->getProviderName());
            $media->setContext($baseMedia->getContext());
        }else{
            $media = $baseMedia;
        }

        $data = $this->extractBase64String($fileData);

        $file = new UploadedBase64File($data, $fileName, 'image/png');

        if (!($file instanceof FileInterface)) {
            $file = new FilesystemFile($file);
        }

        $media->setBinaryContent($file);

        $provider->transform($media);

        $validator = $this->container->get('validator');

        $errors = $validator->validate($media);

        $errorMessages = [];
        if ($errors->count() > 0) {
            $duplicate = false;
            foreach ($errors as $error) {
                $errorMessages[] = $error->getMessage();

                if ($error->getMessage() == 'File is duplicate') {
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
            if($clone){
                $this->admin->create($media);
            }else{
                $this->admin->update($media);
            }
            $path = $this->admin->generateObjectUrl('edit', $media);
            $response->offsetSet('url', $path);
            $response->offsetSet('id', $media->getId());
        } catch (\Exception $e) {
            throw new UploadException($e->getMessage());
        }
    }

    public static function getSubscribedServices(): array
    {
        return [
                'networking_init_cms.admin.tag' => TagAdmin::class,
                'sonata.media.pool' => Pool::class,
                'sonata.media.manager.category' => '?'.CategoryManagerInterface::class,
                'sonata.media.manager.context' => '?'.ContextManagerInterface::class,
            ] + parent::getSubscribedServices();
    }
}
