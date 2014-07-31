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

use Networking\InitCmsBundle\Doctrine\Extensions\Versionable\ResourceVersionInterface;
use Networking\InitCmsBundle\Doctrine\Extensions\Versionable\VersionableInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
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

    public function getClass(){
        return $this->class;
    }

    /**
     * {@inheritDoc}
     */
    public function getRouteByName($name, $parameters = array())
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
    public function getRouteCollectionForRequest(Request $request)
    {
        $url = $request->getPathInfo();

        return $this->findContentRoutesBy($url);
    }

    /**
     * {@inheritDoc}
     */
    public function getRoutesByNames($names, $parameters = array())
    {
        $collection = new RouteCollection();

        foreach ($names as $name) {
            $collection->add($name, $this->getRouteByName($name, $parameters));
        }

        return $collection;
    }

    /**
     * {@inheritDoc}
     */
    public function findManyByUrl($url)
    {
        return $this->findContentRoutesBy($url);
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
}
