<?php

declare(strict_types=1);

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Controller;

use Doctrine\Persistence\ManagerRegistry;
use Networking\InitCmsBundle\Admin\MediaAdmin;
use Networking\InitCmsBundle\Admin\TagAdmin;
use Networking\InitCmsBundle\Cache\PageCacheInterface;
use Networking\InitCmsBundle\Component\EventDispatcher\CmsEventDispatcher;
use Networking\InitCmsBundle\Entity\Media;
use Networking\InitCmsBundle\Entity\Tag;
use phpDocumentor\Reflection\DocBlock\Tags\Method;
use Sonata\MediaBundle\Provider\Pool;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Sonata\MediaBundle\Controller\MediaAdminController as BaseMediaAdminController;

class CkeditorAdminController extends CRUDController
{
    protected $admin;

    protected $doctrine;

    public function __construct(
        CmsEventDispatcher $dispatcher,
        PageCacheInterface $pageCache,
        MediaAdmin $mediaAdmin,
        ManagerRegistry $doctrine
    ) {
        parent::__construct($dispatcher, $pageCache);
        $this->doctrine = $doctrine;
    }

    /**
     * @return Response
     *
     * @throws \Twig\Error\RuntimeError
     */
    public function browser(Request $request)
    {
        $this->checkIfMediaBundleIsLoaded();

        $this->admin->checkAccess('list');

        $datagrid = $this->admin->getDatagrid();
        $datagrid->setValue('context', null, $request->get('context'));
        $datagrid->setValue('providerName', null, $request->get('provider'));

        $formats = [];

        foreach ($datagrid->getResults() as $media) {
            $formats[$media->getId()] = $this->container->get('sonata.media.pool')->getFormatNamesByContext($media->getContext());
        }
        $persistentParameters = $this->admin->getPersistentParameters();

        $filterPersister = $this->admin->getFilterPersister();
        $filters = $filterPersister->get($this->admin->getCode());
        $formView = $datagrid->getForm()->createView();

        $tagFilter = $datagrid->getFilter('tags');
        $selectedTag = false;

        if(array_key_exists('tags', $filters) && array_key_exists('value', $filters['tags'])){
            $selectedTag = (int)$filters['tags']['value'];
        }

        $this->container->get('twig')->getRuntime(FormRenderer::class)->setTheme($formView, $this->admin->getFilterTheme());

        $tags = $this->doctrine
            ->getRepository(Tag::class)
            ->createQueryBuilder('t')
            ->select('t', 'c')
            ->leftJoin('t.children', 'c') // preload
            ->orderBy('t.path', 'ASC')
            ->getQuery()->getResult();

        $tagAdmin = $this->container->get('networking_init_cms.admin.tag');
        return $this->renderWithExtraParams(
            $this->getTemplate('browser'),
            [
                'media_pool' => $this->container->get('sonata.media.pool'),
                'persistent_parameters' => $this->admin->getPersistentParameters(),
                'tags' => $tags,
                'tagAdmin' => $tagAdmin,
                'lastItem' => 0,
                'action' => 'browser',
                'form' => $formView,
                'datagrid' => $datagrid,
                'formats' => $formats,
                'csrf_token' => $this->getCsrfToken('sonata.batch'),
                'tags' => $tags,
                'tagAdmin' => $tagAdmin,
                'tagJson' => $tagAdmin->getTagTree($selectedTag),
            ]
        );
    }

    /**
     * @param string  $type
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function upload(Request $request, $type = 'image')
    {
        $errorMessages = [];
        $this->checkIfMediaBundleIsLoaded();

        $this->admin->checkAccess('create');


        $this->admin->setRequest($request);

        $provider = $request->get('provider');
        $file = $request->files->get('upload');

	    $message = $this->trans(
		    'The file is not readable.',[], 'validators'
	    );
	    $response = ['uploaded' => 0, 'error'  => ['message' =>  $message]];


        if (!$provider || null === $file) {
	        return new JsonResponse($response);
        }

        if ($file instanceof UploadedFile && $file->isValid()) {

            try {
                $pool = $this->container->get('sonata.media.pool');
                $context = $request->get('context', $pool->getDefaultContext());

	            /** @var Media $media */
	            $media = $this->admin->getNewInstance();
	            $media->setEnabled(true);
	            $media->setName($file->getClientOriginalName());
                $media->setBinaryContent($file);
                $media->setContext($context);
                $media->setProviderName($provider);


	            $provider = $pool->getProvider($provider);

	            $provider->transform($media);


                $errors = $this->admin->validate($media);

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
			            $path = $provider->generatePublicUrl($originalMedia, 'reference');
			            $response = [
			            	'uploaded' => 1,
				            'fileName' => $originalMedia->getMetadataValue('filename'),
				            'url' => $path,
				            'error'  => ['message' => 'duplicate media']];
		            }else{
			            $response = ['uploaded' => 0, 'error'  => ['message' =>  implode(', ', $errorMessages)]];
		            }

		            return new JsonResponse($response);
	            }

	            try {
                    $this->admin->create($media);
		            $path = $provider->generatePublicUrl($media, 'reference');
                    $response = ['uploaded' => 1, 'fileName' => $media->getMetadataValue('filename'), 'url' => $path ];
	            } catch (\Exception $e) {
		            $response = ['uploaded' => 0, 'error'  => ['message' => $e->getMessage()]];
	            }

            } catch (\Exception|\Throwable $e) {

	            $response = ['uploaded' => 0, 'error'  => ['message' => $e->getMessage()]];
            }
        } elseif ($file instanceof UploadedFile && !$file->isValid()) {

	        $response = ['uploaded' => 0, 'error'  => ['message' =>  $file->getError()]];
        } else {
            $message = $this->trans(
                'error.file_upload_size',
                ['%max_server_size%' => ini_get('upload_max_filesize')], 'media'
            );

	        $response = ['uploaded' => 0, 'error'  => ['message' =>  $message]];
        }

        return new JsonResponse($response);
    }

    /**
     * @return Response
     *
     * @throws \Twig\Error\RuntimeError
     */
    public function browserRefresh()
    {
        $this->checkIfMediaBundleIsLoaded();

        $this->admin->checkAccess('list');

        $datagrid = $this->admin->getDatagrid($this->admin->getPersistentParameter('context'), $this->admin->getPersistentParameter('provider'));
        $datagrid->setValue('context', null, $this->admin->getPersistentParameter('context'));
        $datagrid->setValue('providerName', null, $this->admin->getPersistentParameter('provider'));

        $formats = [];

        foreach ($datagrid->getResults() as $media) {
            $formats[$media->getId()] = $this->container->get('sonata.media.pool')->getFormatNamesByContext($media->getContext());
        }

        $formView = $datagrid->getForm()->createView();

        $this->container->get('twig')->getRuntime(FormRenderer::class)->setTheme($formView, $this->admin->getFilterTheme());

        $tags = $this->doctrine
            ->getRepository(Tag::class)
            ->createQueryBuilder('t')
            ->select('t', 'c')
            ->leftJoin('t.children', 'c') // preload
            ->orderBy('t.path', 'ASC')
            ->getQuery()->getResult();

        $tagAdmin = $this->container->get('networking_init_cms.admin.tag');

        return $this->renderWithExtraParams(
            '@NetworkingInitCms/Ckeditor/list_items.html.twig',
            [
                'media_pool' => $this->container->get('sonata.media.pool'),
                'persistent_parameters' => $this->admin->getPersistentParameters(),
                'tags' => $tags,
                'tagAdmin' => $tagAdmin,
                'lastItem' => 0,
                'action' => 'browser',
                'form' => $formView,
                'datagrid' => $datagrid,
                'formats' => $formats,
            ]
        );
    }

    /**
     * Returns a template.
     *
     * @param string $name
     *
     * @return string
     */
    private function getTemplate($name)
    {
        $templates = [
            'browser' => '@NetworkingInitCms/Ckeditor/list.html.twig',
            'upload' => '@NetworkingInitCms/Ckeditor/upload.html.twig'
        ];

        if (isset($templates[$name])) {
            return $templates[$name];
        }

        return null;
    }

    /**
     * Checks if SonataMediaBundle is loaded otherwise throws an exception.
     *
     * @throws \RuntimeException
     */
    private function checkIfMediaBundleIsLoaded()
    {
        $bundles = $this->getParameter('kernel.bundles');

        if (!isset($bundles['SonataMediaBundle'])) {
            throw new \RuntimeException('You cannot use this feature because you have to use SonataMediaBundle');
        }
    }

    public static function getSubscribedServices(): array
    {
        return [
                'networking_init_cms.admin.tag' => TagAdmin::class,
                'sonata.media.pool' => Pool::class,
                'sonata.media.admin.media' => MediaAdmin::class,
            ] + parent::getSubscribedServices();
    }
}
