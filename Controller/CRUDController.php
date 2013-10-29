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
use Sonata\AdminBundle\Controller\CRUDController as SonataCRUDController;
use Networking\InitCmsBundle\Entity\LastEditedListener as ORMLastEditedListener;
use Networking\InitCmsBundle\Document\LastEditedListener as ODMLastEditedListener;
use Networking\InitCmsBundle\Component\EventDispatcher\CmsEventDispatcher;
use Networking\InitCmsBundle\Component\EventDispatcher\CmsEvent;

/**
 * Class CRUDController
 * @package Networking\InitCmsBundle\Controller
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class CRUDController extends SonataCRUDController
{
    CONST EDIT_ENTITY = 'crud_controller.edit_entity';

    /**
     * @var CmsEventDispatcher $dispatcher
     */
    protected $dispatcher;

    /**
     * Set up the lasted edited dispatcher
     */
    public function configure()
    {
        parent::configure();
        $this->dispatcher = $this->get('networking_init_cms.event_dispatcher');

        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $this->get('session');

        switch(strtolower($this->container->getParameter('networking_init_cms.db_driver'))){
            case 'monodb':
                $lastEditedSubscriber = new ODMLastEditedListener($session);
                break;
            case 'orm':
                $lastEditedSubscriber = new ORMLastEditedListener($session);
                break;
            default:
                $lastEditedSubscriber = false;
                break;
        }

        if($lastEditedSubscriber){
            $this->dispatcher->addSubscriber($lastEditedSubscriber);
        }
    }

    /**
     * @param string $view
     * @param array $parameters
     * @param Response $response
     * @return Response
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
