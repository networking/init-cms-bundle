<?php
/**
 * This file is part of the init_cms_sandbox package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Networking\InitCmsBundle\Model;

use Networking\InitCmsBundle\Component\Routing\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Cmf\Component\Routing\RouteProviderInterface;


/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class ContentRouteManager implements ContentRouteManagerInterface, RouteProviderInterface
{

    /**
     * @var string $className
     */
    protected $className;

    /**
     * @var string
     */
    protected $class;

    /**
     * @var Request
     */
    protected $request;


    /**
     * @var \Symfony\Component\DependencyInjection\ContainerInterface $container
     */
    protected $container;


    /**
     * {@inheritDoc}
     */
    public function setClassName($className = null)
    {
        $this->className = $className;
    }

    public function getClassName()
    {
        return $this->className;
    }

    /**
     * {@inheritDoc}
     */
    public function getRouteByName($name)
    {

        throw new RouteNotFoundException("No route found for name '$name'");

    }



    /**
     * {@inheritDoc}
     */
    public function getRoutesByNames($names)
    {

        return  new RouteCollection();

    }


    /**
     * @param ContentRouteInterface $contentRoute
     */
    public function initializeContentRoute(ContentRouteInterface &$contentRoute)
    {
        $content = $this->getRouteContent($contentRoute);

        $contentRoute->setContent($content);
    }

    /**
     * @param  ContentRouteInterface $contentRoute
     * @return object
     */
    public function getRouteContent(ContentRouteInterface $contentRoute)
    {

        return $this->findContentByContentRoute($contentRoute);
    }

    /**
     * @param ContentRouteInterface $contentRoute
     * @param $path
     * @param $content
     * @return Route
     */
    public static function generateRoute(ContentRouteInterface $contentRoute, $path, $content){


        $template = new Template(['template' => $contentRoute->getTemplate()]);
        $defaults = [
            'route_params' => '',
            Route::LOCALE => $contentRoute->getLocale(),
            RouteObjectInterface::CONTROLLER_NAME => $contentRoute->getController(),
            RouteObjectInterface::TEMPLATE_NAME => $template,
            RouteObjectInterface::CONTENT_OBJECT => $content
        ];

        return new Route($path, $defaults);
    }

}
