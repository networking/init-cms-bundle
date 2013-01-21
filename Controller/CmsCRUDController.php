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
    Sonata\AdminBundle\Controller\CRUDController,
    Sonata\AdminBundle\Admin\Admin as SontataAdmin,
    Networking\InitCmsBundle\Component\EventDispatcher\CmsEventDispatcher,
    Networking\InitCmsBundle\Component\EventDispatcher\CmsEvent,
    Networking\InitCmsBundle\EventListener\LastEditedListener;

/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class CmsCRUDController extends CRUDController
{
    CONST EDIT_ENTITY = 'crud_controller.edit_entity';

    /**
     * @var CmsEventDispatcher $dispatcher
     */
    protected $dispatcher;

    public function configure()
    {
        parent::configure();

        $this->dispatcher = $this->get('networking_ini_cms.event_dispatcher');
        $lastEditedSubscriber = new LastEditedListener($this->get('session'));
        $this->dispatcher->addSubscriber($lastEditedSubscriber);
    }

    /**
     * @param mixed   $data
     * @param integer $status
     * @param array   $headers
     *
     * @return Response with json encoded data
     */
    public function renderJson($data, $status = 200, $headers = array())
    {


        // fake content-type so browser does not show the download popup when this
        // response is rendered through an iframe (used by the jquery.form.js plugin)
        //  => don't know yet if it is the best solution
        if ($this->get('request')->get('_xml_http_request')
            && strpos($this->get('request')->headers->get('Content-Type'), 'multipart/form-data') === 0
        ) {
            $headers['Content-Type'] = 'text/plain';
            return new Response(json_encode($data), $status, $headers);
        } else {
            return new JsonResponse($data, $status, $headers);
        }
    }

    public function render($view, array $parameters = array(), Response $response = null)
    {
        if (array_key_exists('action', $parameters) && $parameters['action'] == 'edit') {

            $event = new CmsEvent($parameters['object']);

            $this->dispatcher->dispatch(CmsCRUDController::EDIT_ENTITY, $event);
        }


        return parent::render($view, $parameters, $response);
    }
}
