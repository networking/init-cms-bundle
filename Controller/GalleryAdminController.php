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

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;
use Sonata\MediaBundle\Controller\GalleryAdminController as SonataGalleryAdminController;

/**
 * Class GalleryAdminController
 * @package Networking\InitCmsBundle\Controller
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class GalleryAdminController extends SonataGalleryAdminController
{
    /**
     * Override the standard list view
     * @return Response
     * @throws AccessDeniedException
     */
    public function listAction()
    {
        if (false === $this->admin->isGranted('LIST')) {
            throw new AccessDeniedException();
        }

        $pool = $this->container->get('sonata.media.pool');
        $galleryForm = array();
        $galleryGrid = array();

        foreach ($pool->getContexts() as $context => $value) {
            $tempgrid = $this->admin->getDatagrid($context);
            $tempgrid->setValue('context', null, $context);
            $galleryForm[$context] = $tempgrid->getForm()->createView();
            $galleryGrid[$context] = $tempgrid;

            $this->get('twig')->getExtension('form')->renderer->setTheme($galleryForm[$context], $this->admin->getFilterTheme());
        }
        $dataGrid = $this->admin->getDatagrid();
        $formView = $dataGrid->getForm()->createView();

        $this->get('twig')->getExtension('form')->renderer->setTheme($formView, $this->admin->getFilterTheme());

        return $this->render(
            $this->admin->getTemplate('list'),
            array(
                'action' => 'list',
                'mainDataGrid' => $dataGrid,
                'form' => $formView,
                'datagrid' => $galleryGrid,
                'mediaform' => $galleryForm,

            )
        );
    }
}
