<?php

declare(strict_types=1);

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
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\HttpFoundation\Response;
use Sonata\AdminBundle\Controller\CRUDController as SonataCRUDController;
use Networking\InitCmsBundle\Component\EventDispatcher\CmsEventDispatcher;
use Networking\InitCmsBundle\Component\EventDispatcher\CmsEvent;
use Symfony\Contracts\EventDispatcher\EventDispatcherInterface;

/**
 * Class CRUDController.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class CRUDController extends SonataCRUDController
{
    final public const EDIT_ENTITY = 'crud_controller.edit_entity';

    /**
     * @var EventDispatcherInterface
     */
    protected $dispatcher;

    /**
     * @var PageCacheInterface
     */
    protected $pageCache;

    /**
     * CRUDController constructor.
     */
    public function __construct(EventDispatcherInterface $dispatcher, PageCacheInterface $pageCache)
    {
        $this->dispatcher = $dispatcher;
        $this->pageCache = $pageCache;
    }
    

    /**
     * @param string   $view
     * @param array    $parameters
     * @param Response $response
     *
     * @return Response
     */
    protected function addRenderExtraParams(array $parameters = []): array
    {


        if (array_key_exists('action', $parameters) && $parameters['action'] === 'edit') {
            $event = new CmsEvent($parameters['object']);

            $this->dispatcher->dispatch($event);
        }

        return parent::addRenderExtraParams($parameters);
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
        $translationDomain = $domain ?: $this->admin->getTranslationDomain();

        return $this->container->get('translator')->trans($string, $params, $translationDomain);
    }

}
