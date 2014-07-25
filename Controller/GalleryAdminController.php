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
     * return the Response object associated to the list action
     *
     * @return Response
     */
    public function listAction()
    {
        if (false === $this->admin->isGranted('LIST')) {
            throw new AccessDeniedException();
        }
        /** @var \Sonata\MediaBundle\Provider\Pool $mediaPool */
        $mediaPool = $this->container->get('sonata.media.pool');

        $contexts = $mediaPool->getContexts();

        reset($contexts);
        $contextName = key($contexts);
        $request = $this->getRequest();
        $datagrid = $this->admin->getDatagrid($request->get('context', $contextName));
        $datagrid->setValue('context', null, $this->admin->getPersistentParameter('context'));

        $formView = $datagrid->getForm()->createView();

        // set the theme for the current Admin Form
        $this->get('twig')->getExtension('form')->renderer->setTheme($formView, $this->admin->getFilterTheme());

        return $this->render(
            $this->admin->getTemplate('list'),
            array(
                'action' => 'list',
                'form' => $formView,
                'datagrid' => $datagrid,
                'csrf_token' => $this->getCsrfToken('sonata.batch'),
            )
        );
    }
}
