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


/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class ContentRouteManager implements ContentRouteManagerInterface
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
    public function getRouteByName($name, $parameters = [])
    {
        $dynamicRouteName = self::ROUTE_GENERATE_DUMMY_NAME;


        if ($name !== $dynamicRouteName) {

            throw new RouteNotFoundException("Route name '$name' does not begin with the route name prefix '{ $dynamicRouteName }'");
        }

        $parameters['classType'] = $this->className;

        /** @var $route ContentRouteInterface */
        $route = $this->findContentRouteBy($parameters['route_params']);


        if (!$route) {
            throw new RouteNotFoundException("No route found for name '$name'");
        }

        $this->initializeContentRoute($route);

        return $route;
    }



    /**
     * {@inheritDoc}
     */
    public function getRoutesByNames($names, $parameters = [])
    {

        $collection = new RouteCollection();

        foreach ($names as $name) {
            $collection->add($name, $this->getRouteByName($name, $parameters));
        }

        return $collection;
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
