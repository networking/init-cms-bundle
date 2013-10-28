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

use Sonata\AdminBundle\Exception\ModelManagerException,
    Sonata\MediaBundle\Controller\MediaAdminController as SonataMediaAdminController,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpFoundation\JsonResponse,
    Sonata\AdminBundle\Admin\Admin as SontataAdmin,
    Sonata\MediaBundle\Admin\ORM\MediaAdmin,
    Sonata\MediaBundle\Provider\MediaProviderInterface,
    Symfony\Component\HttpFoundation\File\UploadedFile,
    Symfony\Component\HttpFoundation\RedirectResponse,
    Symfony\Component\HttpKernel\Exception\NotFoundHttpException,
    Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class MediaAdminController extends SonataMediaAdminController
{


    /**
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @param string $providerName
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     */
    public function doFileUpload(Request $request, $providerName = 'sonata.media.provider.image')
    {
        if ($request->get('CKEditor')) {
            return $this->ckeditorUploadAction($request, $providerName);
        }

        $file = $request->files->get('file');

        $result = $this->handelMediaUpload($file, $providerName);
        $status = $result['status'];
        $params = array(
            'filelink' => $result['url'],
            'error' => $result['message']
        );


        return new JsonResponse($params, $status);
    }

    public function ckeditorUploadAction(Request $request, $providerName = 'sonata.media.provider.image')
    {
        $file = $request->files->get('upload');

        // Required: anonymous function reference number as explained above.
        $funcNum = $request->get('CKEditorFuncNum');

        $params = $this->handelMediaUpload($file, $providerName);
        $status = $params['status'];
        $url = $params['url'];
        $message = $params['message'];

        $response = "<script type='text/javascript'>window.parent.CKEDITOR.tools.callFunction($funcNum, '$url', '$message');</script>";

        return new Response($response, $status);

    }

    public function handelMediaUpload(UploadedFile $file, $providerName)
    {
        $url = '';
        
        $locale = $this->getRequest()->getLocale();

        $session = $this->getRequest()->getSession();

        $pageId = $session->get('Page.last_edited');

        $pageManager = $this->get('networking_init_cms.page_manager');
        $page = $pageManager->findById($pageId);

        if ($page) {
            $locale = $page->getLocale();
        }

        if ($file instanceof UploadedFile && $file->isValid()) {

            try {

                /** @var $mediaAdmin \Sonata\MediaBundle\Admin\ORM\MediaAdmin */
                $mediaAdmin = $this->get('sonata.media.admin.media');

                /** @var $provider \Sonata\MediaBundle\Provider\MediaProviderInterface */
                $provider = $this->get($providerName);

                $context = $mediaAdmin->getPool()->getDefaultContext();

                $mediaClass = $mediaAdmin->getClass();

                /** @var $media \Sonata\MediaBundle\Model\MediaInterface */
                $media = new $mediaClass();

                $media->setProviderName($provider->getName());

                $media->setContext($context);

                $media->setEnabled(true);

                if (method_exists($media, 'setLocale')) {
                    $media->setLocale($locale);
                }

                $media->setName($file->getClientOriginalName());

                $media->setBinaryContent($file);

                $mediaAdmin->getModelManager()->create($media);
                $mediaAdmin->createObjectSecurity($media);

                // Check the $_FILES array and save the file. Assign the correct path to a variable ($url).
                $url = $this->generateUrl('sonata_media_download', array('id' => $media->getId())) . '/' . $media->getMetadataValue('filename');

                // Usually you will only assign something here if the file could not be uploaded.
                $message = '';


                $status = 200;
            } catch (\Exception $e) {

                $message = $e->getMessage();
                $status = 500;
            }
        } elseif ($file instanceof UploadedFile && !$file->isValid()) {
            $message = $file->getError();
            $status = 500;
        } else {
            $message = $this->admin->trans(
                'error.file_upload_size',
                array('%max_server_size%' => ini_get('upload_max_filesize'))
            );
            $status = 500;
        }

        return array('url' => $url, 'message' => $message, 'status' => $status);
    }


    public function uploadedTextBlockImageAction(Request $request)
    {
        $status = 200;

        //$mediaRepository = $this->getDoctrine()->getRepository('NetworkingInitCmsBundle:Media');

        //$media = $mediaRepository->findBy(array('providerName' => 'sonata.media.provider.image'));

        $media = $this->admin->getModelManager()->findBy(array('providerName' => 'sonata.media.provider.image'));

        $provider = $this->get('sonata.media.provider.image');

        $array = array();

        if ($request->get('CKEditor')) {
            $funcNum = $request->get('CKEditorFuncNum');
            // Optional: instance name (might be used to load a specific configuration file or anything else).
            $CKEditor = $request->get('CKEditor');
            // Optional: might be used to provide localized messages.
            $langCode = $request->get('langCode');

            foreach ($media as $image) {
                $tags = $image->getTags();
                if ($tags->count() > 0) {
                    foreach ($tags as $tag) {
                        $array[$tag->getName()][] = array(
                            'reference' => $provider->generatePublicUrl($image, 'reference'),
                            'media' => $image,
                            'title' => $image->getName(),
                            'folder' => $tag->getName(),
                        );
                    }

                } else {
                    $array['Default'][] = array(
                        'reference' => $provider->generatePublicUrl($image, 'reference'),
                        'media' => $image,
                        'title' => $image->getName(),
                        'folder' => 'default',
                    );
                }
            }

            uksort(
                $array,
                function ($a, $b) {
                    if ($a == 'Default') {
                        $a = 0;
                    }
                    if ($b == 'Default') {
                        $b = 100000;
                    }

                    return strtolower($a) > strtolower($b);
                }
            );


            return $this->render(
                'NetworkingInitCmsBundle:MediaAdmin:media_image_browser.html.twig',
                array('media' => $array, 'funcNum' => $funcNum)
            );
        }

        foreach ($media as $image) {
            $tags = $image->getTags();
            if ($tags->count() > 0) {
                foreach ($tags as $tag) {
                    $array[] = array(
                        'thumb' => $provider->generatePublicUrl($image, 'default_small'),
                        'image' => $provider->generatePublicUrl($image, 'reference'),
                        'title' => $image->getName(),
                        'folder' => $tag->getName(),
                    );
                }

            } else {
                $array[] = array(
                    'thumb' => $provider->generatePublicUrl($image, 'default_small'),
                    'image' => $provider->generatePublicUrl($image, 'reference'),
                    'title' => $image->getName(),
                    'folder' => 'default',
                );
            }
        }

        return new JsonResponse($array, $status);
    }


    /**
     * redirect the user depend on this choice
     *
     * @param object $object
     *
     * @return Response
     */
    public function redirectTo($object)
    {
        $url = false;

        if ($this->get('request')->get('btn_update_and_list')) {
            $url = $this->admin->generateUrl('list', array('active_tab' => $this->get('request')->get('context')));
        }
        if ($this->get('request')->get('btn_create_and_list')) {
            $url = $this->admin->generateUrl('list', array('active_tab' => $this->get('request')->get('context')));
        }


        if ($this->get('request')->get('btn_create_and_create')) {
            $params = array();
            if ($this->admin->hasActiveSubClass()) {
                $params['subclass'] = $this->get('request')->get('subclass');
            }
            $url = $this->admin->generateUrl('create', $params);
        }

        if (!$url) {
            $url = $this->admin->generateObjectUrl('edit', $object);
        }

        return new RedirectResponse($url);
    }

    /**
     *
     * @throws AccessDeniedException
     * @return \Symfony\Bundle\FrameworkBundle\Controller\Response|\Symfony\Component\HttpFoundation\Response
     */
    public function createAction()
    {
        if (false === $this->admin->isGranted('CREATE')) {
            throw new AccessDeniedException();
        }

        $parameters = $this->admin->getPersistentParameters();

        if (!$parameters['provider']) {
            return $this->render(
                'NetworkingInitCmsBundle:MediaAdmin:select_provider.html.twig',
                array(
                    'providers' => $this->get('sonata.media.pool')->getProvidersByContext(
                        $this->get('request')->get('context', $this->get('sonata.media.pool')->getDefaultContext())
                    ),
                    'base_template' => $this->getBaseTemplate(),
                    'admin' => $this->admin,
                    'action' => 'create'
                )
            );
        }
        return parent::createAction();
    }

    /**
     *
     * @param mixed $id
     *
     * @throws NotFoundHttpException
     * @throws AccessDeniedException
     * @return Response|RedirectResponse
     */
    public function deleteAction($id)
    {
        $id = $this->get('request')->get($this->admin->getIdParameter());
        $object = $this->admin->getObject($id);

        if (!$object) {
            throw new NotFoundHttpException(sprintf('unable to find the object with id : %s', $id));
        }

        if (false === $this->admin->isGranted('DELETE', $object)) {
            throw new AccessDeniedException();
        }

        if ($this->getRequest()->getMethod() == 'DELETE') {
            try {
                $this->admin->delete($object);
                $this->get('session')->getFlashBag()->add('sonata_flash_success', 'flash_delete_success');
            } catch (ModelManagerException $e) {
                $this->get('session')->getFlashBag()->add('sonata_flash_error', 'flash_delete_error');
            }

            return new RedirectResponse($this->admin->generateUrl(
                'list',
                array('active_tab' => $this->get('request')->get('context'))
            ));
        }

        return $this->render(
            $this->admin->getTemplate('delete'),
            array(
                'object' => $object,
                'action' => 'delete'
            )
        );
    }


    /**
     * Handle uploads from the redactor editor
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     */
    public function uploadTextBlockImageAction(Request $request)
    {
        return $this->doFileUpload($request);
    }

    public function uploadedTextBlockFileAction(Request $request)
    {
        $status = 200;

        //$mediaRepository = $this->getDoctrine()->getRepository('NetworkingInitCmsBundle:Media');

        //$media = $mediaRepository->findBy(array('providerName' => 'sonata.media.provider.file'));

        $media = $this->admin->getModelManager()->findBy(array('providerName' => 'sonata.media.provider.file'));

        $provider = $this->get('sonata.media.provider.file');

        $array = array();

        if ($request->get('CKEditor')) {
            $funcNum = $request->get('CKEditorFuncNum');
            // Optional: instance name (might be used to load a specific configuration file or anything else).
            $CKEditor = $request->get('CKEditor');
            // Optional: might be used to provide localized messages.
            $langCode = $request->get('langCode');

            foreach ($media as $file) {
                $tags = $file->getTags();
                if ($tags->count() > 0) {
                    foreach ($tags as $tag) {
                        $array[$tag->getName()][] = array(
                            'path' => $this->generateUrl('sonata_media_download', array('id' => $file->getId())) . '/' . $file->getMetadataValue('filename'),
                            'content_type' => $file->getContentType(),
                            'title' => $file->getName(),
                        );
                    }

                } else {
                    $array['Default'][] = array(
                        'path' => $this->generateUrl('sonata_media_download', array('id' => $file->getId())) . '/' . $file->getMetadataValue('filename'),
                        'content_type' => $file->getContentType(),
                        'title' => $file->getName(),
                    );
                }
            }

            return $this->render(
                'NetworkingInitCmsBundle:MediaAdmin:media_file_browser.html.twig',
                array('media' => $array, 'funcNum' => $funcNum, 'langCode' => $langCode)
            );
        }


        foreach ($media as $file) {
            $tags = $file->getTags();
            if ($tags->count() > 0) {
                foreach ($tags as $tag) {
                    $array[] = array(
                        'path' => $this->generateUrl('sonata_media_download', array('id' => $file->getId())) . '/' . $file->getMetadataValue('filename'),
                        'content_type' => $file->getContentType(),
                        'title' => $file->getName(),
                        'folder' => $tag->getName(),
                    );
                }

            } else {
                $array[] = array(
                    'path' => $this->generateUrl('sonata_media_download', array('id' => $file->getId())) . '/' . $file->getMetadataValue('filename'),
                    'content_type' => $file->getContentType(),
                    'title' => $file->getName(),
                    'folder' => 'default',
                );
            }
        }

        return new JsonResponse($array, $status);
    }


    /**
     * Handle uploads from the redactor editor
     *
     * @param \Symfony\Component\HttpFoundation\Request $request
     * @return \Symfony\Component\HttpFoundation\JsonResponse
     *
     */
    public function uploadTextBlockFileAction(Request $request)
    {
        return $this->doFileUpload($request, 'sonata.media.provider.file');
    }

    /**
     * return the Response object associated to the list action
     *
     * @return \Symfony\Bundle\FrameworkBundle\Controller\Response|\Symfony\Component\HttpFoundation\Response
     * @throws AccessDeniedException
     */
    public function listAction()
    {
        if (false === $this->admin->isGranted('LIST')) {
            throw new AccessDeniedException();
        }

        $pool = $this->container->get('sonata.media.pool');
        $galleryListMode = $this->getRequest()->get('pcode') ? true : false;
        $mediaForm = array();
        $mediaGrid = array();

        foreach ($pool->getContexts() as $context => $value) {

            if ($galleryListMode) {
                if ($context != $this->getRequest()->get('context')) {
                    continue;
                }
            }
            $tempgrid = $this->admin->getDatagrid($context);
            $tempgrid->setValue('providerName', null, $this->admin->getPersistentParameter('provider'));
            $tempgrid->setValue('context', null, $context);

            $mediaForm[$context] = $tempgrid->getForm()->createView();
            $mediaGrid[$context] = $tempgrid;

            $this->get('twig')->getExtension('form')->renderer->setTheme(
                $mediaForm[$context],
                array('NetworkingInitCmsBundle:Form:form_admin_fields.html.twig')
            );
        }

        $dataGrid = $this->admin->getDatagrid();
        $dataGrid->setValue('providerName', null, $this->admin->getPersistentParameter('provider'));
        $formView = $dataGrid->getForm()->createView();

        $this->get('twig')->getExtension('form')->renderer->setTheme(
            $formView,
            array('NetworkingInitCmsBundle:Form:form_admin_fields.html.twig')
        );


        return $this->render(
            $this->admin->getTemplate('list'),
            array(
                'action' => 'list',
                'mainDataGrid' => $dataGrid,
                'form' => $formView,
                'datagrid' => $mediaGrid,
                'mediaform' => $mediaForm,
                'galleryListMode' => $galleryListMode,
                'csrf_token' => $this->getCsrfToken('sonata.batch')

            )
        );
    }

    public function showAction($id = null)
        {
            if (false === $this->admin->isGranted('SHOW')) {
                throw new AccessDeniedException();
            }

            $media = $this->admin->getObject($id);

            if (!$media) {
                throw new NotFoundHttpException('unable to find the media with the id');
            }

            return $this->render('NetworkingInitCmsBundle:MediaAdmin:show.html.twig', array(
                'media'         => $media,
                'formats'       => $this->get('sonata.media.pool')->getFormatNamesByContext($media->getContext()),
                'format'        => $this->get('request')->get('format', 'reference'),
                'base_template' => $this->getBaseTemplate(),
                'admin'         => $this->admin,
                'security'      => $this->get('sonata.media.pool')->getDownloadSecurity($media),
                'action'        => 'view',
                'pixlr'         => $this->container->has('sonata.media.extra.pixlr') ? $this->container->get('sonata.media.extra.pixlr') : false,
            ));
        }
}
