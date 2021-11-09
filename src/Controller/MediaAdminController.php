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
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\HttpFoundation\File\Exception\UploadException;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * Class MediaAdminController.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class MediaAdminController extends SonataMediaAdminController
{
    /**
     * @var TemplateRegistryInterface
     */
    private $templateRegistry;


    public function configure()
    {
        parent::configure();

        $this->templateRegistry = $this->container->get($this->admin->getCode().'.template_registry');
        if (!$this->templateRegistry instanceof TemplateRegistryInterface) {
            throw new \RuntimeException(
                sprintf(
                    'Unable to find the template registry related to the current admin (%s)',
                    $this->admin->getCode()
                )
            );
        }
    }
    /**
     * @param null $id
     *
     * @return Response
     */
    public function showAction($id = null)
    {

        $media = $this->admin->getObject($id);

        $this->admin->checkAccess('show', $media);

        if (!$media) {
            throw new NotFoundHttpException('unable to find the media with the id');
        }

        return $this->renderWithExtraParams(
            '@NetworkingInitCms/MediaAdmin/show.html.twig',
            [
                'media' => $media,
                'formats' => $this->get('sonata.media.pool')->getFormatNamesByContext($media->getContext()),
                'format' => $this->getRequest()->get('format', 'reference'),
                'base_template' => $this->getBaseTemplate(),
                'admin' => $this->admin,
                'security' => $this->get('sonata.media.pool')->getDownloadStrategy($media),
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
    public function createAction(Request $request = null)
    {
        $this->admin->checkAccess('create');

        $parameters = $this->admin->getPersistentParameters();

        if (!array_key_exists('provider', $parameters) || !$parameters['provider']) {
            return $this->render(
                '@NetworkingInitCms/MediaAdmin/select_provider.html.twig',
                [
                    'providers' => $this->get('sonata.media.pool')->getProvidersByContext(
                        $request->get('context', $this->get('sonata.media.pool')->getDefaultContext())
                    ),
                    'base_template' => $this->getBaseTemplate(),
                    'admin' => $this->admin,
                    'action' => 'create',
                ]
            );
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
    public function redirectTo($object)
    {
        $url = false;

        if ($this->getRequest()->get('btn_update_and_list')) {
            $url = $this->admin->generateUrl('list', ['active_tab' => $this->getRequest()->get('context')]);
        }
        if ($this->getRequest()->get('btn_create_and_list')) {
            $url = $this->admin->generateUrl('list', ['active_tab' => $this->getRequest()->get('context')]);
        }

        if ($this->getRequest()->get('btn_create_and_create')) {
            $params = [];
            if ($this->admin->hasActiveSubClass()) {
                $params['subclass'] = $this->getRequest()->get('subclass');
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
    public function deleteAction($id)
    {
        $id = $this->getRequest()->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('DELETE', $object)) {
            throw new AccessDeniedException();
        }
        /** @var Request $request */
        $request = $this->get('request_stack')->getCurrentRequest();
        if ($request->getMethod() == 'DELETE') {
            try {
                $this->admin->delete($object);
                $this->addFlash('sonata_flash_success', $this->trans('flash_delete_success', [], 'SonataAdminBundle'));
            } catch (ModelManagerException $e) {
                $this->addFlash('sonata_flash_error', $this->trans('flash_delete_error', [], 'SonataAdminBundle'));
            }

            return new RedirectResponse($this->admin->generateUrl(
                'list',
                ['active_tab' => $this->getRequest()->get('context')]
            ));
        }

        return $this->render(
            $this->templateRegistry->getTemplate('delete'),
            [
                'object' => $object,
                'action' => 'delete',
            ]
        );
    }

    /**
     * execute a batch delete.
     *
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     *
     * @param \Sonata\AdminBundle\Datagrid\ProxyQueryInterface $query
     *
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function batchActionDelete(ProxyQueryInterface $query)
    {
        $this->admin->checkAccess('batchDelete');

        try {
            $this->doBatchDelete($query);

            $this->addFlash('sonata_flash_success', $this->trans('flash_batch_delete_success', [], 'SonataAdminBundle'));
        } catch (ModelManagerException $e) {
            $this->addFlash('sonata_flash_error', $this->trans('flash_batch_delete_error', [], 'SonataAdminBundle'));
        }

        return new RedirectResponse($this->admin->generateUrl(
            'list',
            ['filter' => $this->admin->getFilterParameters()]
        ));
    }

    public function batchActionAddTags(ProxyQueryInterface $selectedModelQuery)
    {
        $tagAdmin = $this->get('networking_init_cms.admin.tag');
        if (!$this->admin->isGranted('EDIT') || !$this->admin->isGranted('DELETE')) {
            throw new AccessDeniedException();
        }

        $modelManager = $tagAdmin->getModelManager();

        /** @var Tag $tag */
        $tag = $modelManager->find($tagAdmin->getClass(), $this->get('request_stack')->getCurrentRequest()->get('tags'));

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

    /**
     * @param ProxyQueryInterface $queryProxy
     *
     * @throws ModelManagerException
     */
    protected function doBatchDelete(ProxyQueryInterface $queryProxy)
    {
        $modelManager = $this->admin->getModelManager();
        $class = $this->admin->getClass();

        $queryProxy->select('DISTINCT '.$queryProxy->getRootAlias());

        try {
            $entityManager = $modelManager->getEntityManager($class);

            $i = 0;
            foreach ($queryProxy->getQuery()->iterate() as $pos => $object) {
                $entityManager->remove($object[0]);

                if ((++$i % 20) == 0) {
                    $entityManager->flush();
                    $entityManager->clear();
                }
            }

            $entityManager->flush();
            $entityManager->clear();
        } catch (\PDOException $e) {
            throw new ModelManagerException('', 0, $e);
        } catch (DBALException $e) {
            throw new ModelManagerException('', 0, $e);
        }
    }

    /**
     * @param Request|null $request
     *
     * @return Response
     */
    public function listAction(Request $request = null)
    {
        $this->admin->checkAccess('list');

        $galleryListMode = $request->get('pcode') ? true : false;

        $datagrid = $this->admin->getDatagrid();

        $persistentParameters = $this->admin->getPersistentParameters();

        $formView = $datagrid->getForm()->createView();

        $this->get('twig')->getRuntime(FormRenderer::class)->setTheme($formView, $this->admin->getFilterTheme());

        $tags = $this->getDoctrine()
            ->getRepository(Tag::class)
            ->createQueryBuilder('t')
            ->select('t', 'c')
            ->leftJoin('t.children', 'c') // preload
            ->orderBy('t.path', 'ASC')
            ->getQuery()->getResult();

        $tagAdmin = $this->get('networking_init_cms.admin.tag');
        $tagFilter = $datagrid->getFilter('tags');
        $selectedTag = false;
        if($tagFilter->isActive()){
            $values = $datagrid->getValues();
            $selectedTag = (int)$values['tags']['value'];
        }
        return $this->render(
            $this->templateRegistry->getTemplate('list'),
            [
                'providers' => $this->get('sonata.media.pool')->getProvidersByContext(
                    $request->get('context', $persistentParameters['context'])
                ),
                'tags' => $tags,
                'tagJson' => $tagAdmin->getTagTree($selectedTag),
                'tagAdmin' => $tagAdmin,
                'lastItem' => 0,
                'action' => 'list',
                'form' => $formView,
                'datagrid' => $datagrid,
                'galleryListMode' => $galleryListMode,
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
        
        $galleryListMode = $request->get('pcode') ? true : false;
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

        $tagAdmin = $this->get('networking_init_cms.admin.tag');

        return $this->render(
            '@NetworkingInitCms/MediaAdmin/list_items.html.twig',
            [
                'providers' => $this->get('sonata.media.pool')->getProvidersByContext(
                    $request->get('context', $persistentParameters['context'])
                ),
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

        return $this->render('NetworkingInitCmsBundle:MediaAdmin:preview.html.twig', ['object' => $object]);
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

        $request = $this->getRequest();
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

    public function handleUpload($response, $request){
        $request->query->set('oneuploader', true);
        $fileData = $request->request->get('file');
        $clone = $request->request->get('clone');
        $id = $request->request->get('id');

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
        }else{
            $media = $baseMedia;
        }

        $data = $this->extractBase64String($fileData);

        $file = new UploadedBase64File($data, $fileName, 'image/png');

        if (!($file instanceof FileInterface)) {
            $file = new FilesystemFile($file);
        }

        $media->setBinaryContent($file);

        $provider = $this->admin->getPool()->getProvider($media->getProviderName());

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
}
