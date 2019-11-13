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

use Networking\InitCmsBundle\Cache\PageCacheInterface;
use Sonata\AdminBundle\Templating\TemplateRegistryInterface;
use Symfony\Component\HttpFoundation\Response;
use Sonata\AdminBundle\Controller\CRUDController as SonataCRUDController;
use Networking\InitCmsBundle\Entity\LastEditedListener as ORMLastEditedListener;
use Symfony\Component\Form\FormRenderer;
use Symfony\Component\Form\FormView;
use Networking\InitCmsBundle\Component\EventDispatcher\CmsEventDispatcher;
use Networking\InitCmsBundle\Component\EventDispatcher\CmsEvent;

/**
 * Class CRUDController.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class CRUDController extends SonataCRUDController
{
    const EDIT_ENTITY = 'crud_controller.edit_entity';

    /**
     * @var CmsEventDispatcher
     */
    protected $dispatcher;

    /**
     * @var PageCacheInterface
     */
    protected $pageCache;

    /**
     * @var TemplateRegistryInterface
     */
    protected $templateRegistry;

    /**
     * CRUDController constructor.
     * @param CmsEventDispatcher $dispatcher
     * @param PageCacheInterface $pageCache
     */
    public function __construct(CmsEventDispatcher $dispatcher, PageCacheInterface $pageCache)
    {
        $this->dispatcher = $dispatcher;
        $this->pageCache = $pageCache;
    }


    /**
     * Set up the lasted edited dispatcher.
     */
    public function configure()
    {
        parent::configure();

        /** @var \Symfony\Component\HttpFoundation\Session\Session $session */
        $session = $this->get('session');

        switch (strtolower($this->container->getParameter('networking_init_cms.db_driver'))) {
            case 'orm':
                $lastEditedSubscriber = new ORMLastEditedListener($session);
                break;
            default:
                $lastEditedSubscriber = false;
                break;
        }

        if ($lastEditedSubscriber) {
            $this->dispatcher->addSubscriber($lastEditedSubscriber);
        }

        $this->templateRegistry = $this->container->get($this->admin->getCode().'.template_registry');
        if (!$this->templateRegistry instanceof TemplateRegistryInterface) {
            throw new \RuntimeException(
                sprintf(
                    'Unable to find the template registry related to the current admin (%s)',
                    $this->admin->getCode()
                )
            );
        }
    }

    /**
     * @param string   $view
     * @param array    $parameters
     * @param Response $response
     *
     * @return Response
     */
    public function renderWithExtraParams($view, array $parameters = [], Response $response = null)
    {
        if (array_key_exists('action', $parameters) && $parameters['action'] == 'edit') {
            $event = new CmsEvent($parameters['object']);

            $this->dispatcher->dispatch(self::EDIT_ENTITY, $event);
        }

        return parent::renderWithExtraParams($view, $parameters, $response);
    }

    /**
     * @param $string
     * @param array $params
     * @param null  $domain
     *
     * @return mixed
     */
    public function translate($string, $params = [], $domain = null)
    {
        $translationDomain = $domain ? $domain : $this->admin->getTranslationDomain();

        return $this->get('translator')->trans($string, $params, $translationDomain);
    }

    /**
     * @param FormView $formView
     * @param array $theme
     * @throws \Twig\Error\RuntimeError
     */
    protected function setFormTheme(FormView $formView, $theme)
    {
        $twig = $this->get('twig');

        $twig->getRuntime(FormRenderer::class)->setTheme($formView, $theme);
    }
}
