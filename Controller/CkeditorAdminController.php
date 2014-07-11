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

use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

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
        $pool = $this->container->get('sonata.media.pool');

        $formats = array();


        $mediaForm = array();
        $mediaGrid = array();

        foreach ($pool->getContexts() as $context => $value) {

            if ($context != $this->getRequest()->get('context')) {
                continue;
            }
            $tempgrid = $this->admin->getDatagrid($context);
            $tempgrid->setValue('providerName', null, $this->admin->getPersistentParameter('provider'));
            $tempgrid->setValue('context', null, $context);

            $tempgrid->getForm()->createView();
            $mediaGrid[$context] = $tempgrid;

            foreach ($tempgrid->getResults() as $media) {
                $formats[$context][$media->getId()] = $this->get('sonata.media.pool')->getFormatNamesByContext(
                    $media->getContext()
                );
            }
        }

        $dataGrid = $this->admin->getDatagrid();
        $dataGrid->setValue('providerName', null, $this->admin->getPersistentParameter('provider'));

        $formView = $dataGrid->getForm()->createView();


        $this->get('twig')->getExtension('form')->renderer->setTheme($formView, $this->admin->getFilterTheme());

        return $this->render(
            $this->getTemplate('browser'),
            array(
                'action' => 'browser',
                'form' => $formView,
                'formats' => $formats,
                'mainDataGrid' => $dataGrid,
                'datagrid' => $mediaGrid,
                'csrf_token' => $this->getCsrfToken('sonata.batch')
            )
        );
    }

    /**
     * Returns the response object associated with the upload action
     *
     * @return Response
     *
     * @throws NotFoundHttpException
     */
    public function uploadAction()
    {
        $this->checkIfMediaBundleIsLoaded();

        if (false === $this->admin->isGranted('CREATE')) {
            throw new AccessDeniedException();
        }

        $mediaManager = $this->get('sonata.media.manager.media');

        $request = $this->getRequest();
        $provider = $request->get('provider');
        $file = $request->files->get('upload');

        if (!$request->isMethod('POST') || !$provider || null === $file) {
            throw $this->createNotFoundException();
        }

        $context = $request->get('context', $this->get('sonata.media.pool')->getDefaultContext());

        $media = $mediaManager->create();
        $media->setBinaryContent($file);

        $mediaManager->save($media, $context, $provider);
        $this->admin->createObjectSecurity($media);

        return $this->render(
            $this->getTemplate('upload'),
            array(
                'action' => 'list',
                'object' => $media
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
