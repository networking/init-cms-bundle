<?php

/*
 * This file is part of the Sonata package.
 *
 * (c) Thomas Rabaix <thomas.rabaix@sonata-project.org>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Controller;

use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Symfony\Component\HttpFoundation\Request;
use Sonata\MediaBundle\Controller\MediaAdminController as BaseMediaAdminController;

class CkeditorAdminController extends BaseMediaAdminController
{
    /**
     * Returns the response object associated with the browser action
     *
     * @return Response
     *
     * @throws AccessDeniedException
     */
    public function browserAction()
    {
        $this->checkIfMediaBundleIsLoaded();

        if (false === $this->admin->isGranted('LIST')) {
            throw new AccessDeniedException();
        }

        $datagrid = $this->admin->getDatagrid($this->admin->getPersistentParameter('context'));
        $datagrid->setValue('context', null, $this->admin->getPersistentParameter('context'));
        $datagrid->setValue('providerName', null, $this->admin->getPersistentParameter('provider'));

        $formats = array();

        foreach ($datagrid->getResults() as $media) {
            $formats[$media->getId()] = $this->get('sonata.media.pool')->getFormatNamesByContext($media->getContext());
        }

        $formView = $datagrid->getForm()->createView();

        $this->get('twig')->getExtension('form')->renderer->setTheme($formView, $this->admin->getFilterTheme());

        return $this->render(
            $this->getTemplate('browser'),
            array(
                'action' => 'browser',
                'form' => $formView,
                'datagrid' => $datagrid,
                'formats' => $formats
            )
        );
    }


    /**
     * @param string $type
     * @return \Symfony\Bundle\FrameworkBundle\Controller\Response
     * @throws \Symfony\Component\Security\Core\Exception\AccessDeniedException
     * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function uploadAction($type = 'image')
    {
        $this->checkIfMediaBundleIsLoaded();

        if (false === $this->admin->isGranted('CREATE')) {
            throw new AccessDeniedException();
        }

        $filePath = '';
        $mediaManager = $this->get('sonata.media.manager.media');

        /** @var Request $request */
        $request = $this->getRequest();
        $provider = $request->get('provider');
        $file = $request->files->get('upload');

        if (!$request->isMethod('POST') || !$provider || null === $file) {
            throw $this->createNotFoundException();
        }
        if ($file instanceof UploadedFile && $file->isValid()) {

            try {
                $context = $request->get('context', $this->get('sonata.media.pool')->getDefaultContext());

                $media = $mediaManager->create();
                $media->setBinaryContent($file);

                $mediaManager->save($media, $context, $provider);
                $this->admin->createObjectSecurity($media);

                switch ($type) {
                    case 'file':
                        $filePath = $this->generateUrl(
                            'networking_init_cms_file_download',
                            array('id' => $media->getId(), 'name' => $media->getMetadataValue('filename'))
                        );
                        break;
                    default:
                        $provider = $this->get($provider);
                        $filePath = $provider->generatePublicUrl($media, 'reference');
                        break;
                }



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

        return $this->render(
                            $this->getTemplate('upload'),
                            array(
                                'action' => 'list',
                                'filePath' => $filePath,
                                'message' => $message,
                                'status' => $status
                            )
                        );

    }

    /**
     * Returns a template
     *
     * @param string $name
     *
     * @return string
     */
    private function getTemplate($name)
    {
        $templates = $this->container->getParameter('sonata.formatter.ckeditor.configuration.templates');

        if (isset($templates[$name])) {
            return $templates[$name];
        }

        return null;
    }

    /**
     * Checks if SonataMediaBundle is loaded otherwise throws an exception
     *
     * @throws \RuntimeException
     */
    private function checkIfMediaBundleIsLoaded()
    {
        $bundles = $this->container->getParameter('kernel.bundles');

        if (!isset($bundles['SonataMediaBundle'])) {
            throw new \RuntimeException('You cannot use this feature because you have to use SonataMediaBundle');
        }
    }
}
