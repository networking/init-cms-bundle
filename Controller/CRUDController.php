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


use Symfony\Bundle\FrameworkBundle\Controller\Controller,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\HttpFoundation\Response,
    Symfony\Component\HttpKernel\Exception\NotFoundHttpException,
    Symfony\Component\HttpFoundation\JsonResponse,
    Sensio\Bundle\FrameworkExtraBundle\Configuration\Template,
    Symfony\Component\Security\Core\Exception\AccessDeniedException,
    Symfony\Component\HttpFoundation\RedirectResponse,
    Symfony\Component\EventDispatcher\Event,
    Sonata\AdminBundle\Datagrid\ProxyQueryInterface,
    Sonata\AdminBundle\Controller\CRUDController as SonataCRUDController,
    Sonata\AdminBundle\Admin\Admin as SontataAdmin,
    Networking\InitCmsBundle\Component\EventDispatcher\CmsEventDispatcher,
    Networking\InitCmsBundle\Component\EventDispatcher\CmsEvent,
    Networking\InitCmsBundle\EventListener\LastEditedListener;

/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class CRUDController extends SonataCRUDController
{
    CONST EDIT_ENTITY = 'crud_controller.edit_entity';

    /**
     * @var CmsEventDispatcher $dispatcher
     */
    protected $dispatcher;

    public function configure()
    {
        parent::configure();

        $this->dispatcher = $this->get('networking_init_cms.event_dispatcher');
        $lastEditedSubscriber = new LastEditedListener($this->get('session'));
        $this->dispatcher->addSubscriber($lastEditedSubscriber);
    }

    /**
     * @param string $view
     * @param array $parameters
     * @param \Symfony\Component\HttpFoundation\Response $response
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function render($view, array $parameters = array(), Response $response = null)
    {
        if (array_key_exists('action', $parameters) && $parameters['action'] == 'edit') {

            $event = new CmsEvent($parameters['object']);

            $this->dispatcher->dispatch(CRUDController::EDIT_ENTITY, $event);
        }


        return parent::render($view, $parameters, $response);
    }
}
