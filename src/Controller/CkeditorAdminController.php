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

use Networking\InitCmsBundle\Entity\Tag;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\HttpFoundation\File\UploadedFile;
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

        $datagrid = $this->admin->getDatagrid($this->admin->getPersistentParameter('context'), $this->admin->getPersistentParameter('provider'));
        $datagrid->setValue('context', null, $this->admin->getPersistentParameter('context'));
        $datagrid->setValue('providerName', null, $this->admin->getPersistentParameter('provider'));

        $formats = [];

        foreach ($datagrid->getResults() as $media) {
            $formats[$media->getId()] = $this->get('sonata.media.pool')->getFormatNamesByContext($media->getContext());
        }

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

        return $this->render(
            $this->getTemplate('browser'),
            [
                'tags' => $tags,
                'tagAdmin' => $tagAdmin,
                'lastItem' => 0,
                'action' => 'browser',
                'form' => $formView,
                'datagrid' => $datagrid,
                'formats' => $formats
            ]
        );
    }


    /**
     * @param Request $request
     * @param string $type
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function uploadAction(Request $request, $type = 'image')
    {
        $this->checkIfMediaBundleIsLoaded();

        if (false === $this->admin->isGranted('CREATE')) {
            throw new AccessDeniedException();
        }

        $filePath = '';
        $mediaManager = $this->get('sonata.media.manager.media');

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
                            ['id' => $media->getId(), 'name' => $media->getMetadataValue('filename')]
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
                ['%max_server_size%' => ini_get('upload_max_filesize')]
            );
            $status = 500;
        }

        return $this->render(
                            $this->getTemplate('upload'),
                            [
                                'action' => 'list',
                                'filePath' => $filePath,
                                'message' => $message,
                                'status' => $status
                            ]
                        );

    }

    /**
     * @return Response
     * @throws \Twig_Error_Runtime
     */
    public function browserRefreshAction()
    {
        $this->checkIfMediaBundleIsLoaded();

        if (false === $this->admin->isGranted('LIST')) {
            throw new AccessDeniedException();
        }

        $datagrid = $this->admin->getDatagrid($this->admin->getPersistentParameter('context'), $this->admin->getPersistentParameter('provider'));
        $datagrid->setValue('context', null, $this->admin->getPersistentParameter('context'));
        $datagrid->setValue('providerName', null, $this->admin->getPersistentParameter('provider'));

        $formats = [];

        foreach ($datagrid->getResults() as $media) {
            $formats[$media->getId()] = $this->get('sonata.media.pool')->getFormatNamesByContext($media->getContext());
        }

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

        return $this->render(
            'NetworkingInitCmsBundle:Ckeditor:browser_list_items.html.twig',
            [
                'tags' => $tags,
                'tagAdmin' => $tagAdmin,
                'lastItem' => 0,
                'action' => 'browser',
                'form' => $formView,
                'datagrid' => $datagrid,
                'formats' => $formats
            ]
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
