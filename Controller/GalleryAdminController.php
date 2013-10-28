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

use Sonata\MediaBundle\Controller\GalleryAdminController as SonataGalleryAdminController,
    Symfony\Component\Security\Core\Exception\AccessDeniedException;

/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class GalleryAdminController extends SonataGalleryAdminController
{
    /**
     * return the Response object associated to the list action
     *
     * @return Response
     */
    public function oldListAction()
    {
        if (false === $this->admin->isGranted('LIST')) {
            throw new AccessDeniedException();
        }

        $datagrid = $this->admin->getDatagrid();
        $datagrid->setValue('context', null, $this->admin->getPersistentParameter('context'));

        $formView = $datagrid->getForm()->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('form')->renderer->setTheme($formView, $this->admin->getFilterTheme());

        return $this->render(
            $this->admin->getTemplate('list'),
            array(
                'action' => 'list',
                'form' => $formView,
                'datagrid' => $datagrid
            )
        );
    }

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

            $this->get('twig')->getExtension('form')->renderer->setTheme(
                $galleryForm[$context],
                array('NetworkingInitCmsBundle:Form:form_admin_fields.html.twig')
            );
        }
        $dataGrid = $this->admin->getDatagrid();
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
                'datagrid' => $galleryGrid,
                'mediaform' => $galleryForm,

            )
        );
    }
}
