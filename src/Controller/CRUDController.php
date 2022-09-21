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

            $this->dispatcher->dispatch( $event, self::EDIT_ENTITY);
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
        $translationDomain = $domain ? $domain : $this->admin->getTranslationDomain();

        return $this->container->get('translator')->trans($string, $params, $translationDomain);
    }

}
