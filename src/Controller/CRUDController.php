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

use Symfony\Bridge\Twig\AppVariable;
use Symfony\Bridge\Twig\Command\DebugCommand;
use Symfony\Component\HttpFoundation\Response;
use Sonata\AdminBundle\Controller\CRUDController as SonataCRUDController;
use Networking\InitCmsBundle\Entity\LastEditedListener as ORMLastEditedListener;
use Networking\InitCmsBundle\Document\LastEditedListener as ODMLastEditedListener;
use Symfony\Bridge\Twig\Form\TwigRenderer;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\FormView;
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

    public function __construct(CmsEventDispatcher $dispatcher)
    {
        $this->dispatcher = $dispatcher;
    }

    /**
     * Set up the lasted edited dispatcher
     */
    public function configure()
    {
        parent::configure();


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
    public function render($view, array $parameters = [], Response $response = null)
    {
        if (array_key_exists('action', $parameters) && $parameters['action'] == 'edit') {

            $event = new CmsEvent($parameters['object']);

            $this->dispatcher->dispatch(CRUDController::EDIT_ENTITY, $event);
        }
        return parent::renderWithExtraParams($view, $parameters, $response);
    }

    /**
     * @param $string
     * @param array $params
     * @param null $domain
     * @return mixed
     */
    public function translate($string, $params = [], $domain = null)
    {
        $translationDomain = $domain ? $domain : $this->admin->getTranslationDomain();

        return $this->get('translator')->trans($string, $params, $translationDomain);
    }



    /**
     * Sets the admin form theme to form view. Used for compatibility between Symfony versions.
     *
     * @param FormView $formView
     * @param string $theme
     * @throws \Twig_Error_Runtime
     */
    protected function setFormTheme(FormView $formView, $theme)
    {
        $twig = $this->get('twig');

        // BC for Symfony < 3.2 where this runtime does not exists
        if (!method_exists(AppVariable::class, 'getToken')) {
            $twig->getExtension(FormExtension::class)->renderer->setTheme($formView, $theme);

            return;
        }

        // BC for Symfony < 3.4 where runtime should be TwigRenderer
        if (!method_exists(DebugCommand::class, 'getLoaderPaths')) {
            $twig->getRuntime(TwigRenderer::class)->setTheme($formView, $theme);

            return;
        }

        $twig->getRuntime(FormRenderer::class)->setTheme($formView, $theme);
    }
}
