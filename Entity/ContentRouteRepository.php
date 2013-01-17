<?php
/*
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Entity;

use Doctrine\ORM\EntityRepository,
    Symfony\Cmf\Component\Routing\RouteProviderInterface,
    Symfony\Component\HttpFoundation\Session\Session,
    Symfony\Component\Routing\RouteCollection,
    Symfony\Component\Routing\Exception\RouteNotFoundException,
    Symfony\Component\HttpFoundation\Request,
    Networking\InitCmsBundle\Doctrine\Extensions\Versionable\ResourceVersionInterface,
    Networking\InitCmsBundle\Doctrine\Extensions\Versionable\VersionableInterface,
    Networking\InitCmsBundle\Entity\ContentRoute,
    Networking\InitCmsBundle\Entity\Page;

/**
 * @author net working AG <info@networking.ch>
 */
class ContentRouteRepository extends EntityRepository implements RouteProviderInterface
{
    const ROUTE_GENERATE_DUMMY_NAME = 'networking_init_dynamic_route';

    /**
     * @var string $locale
     */
    protected $locale;

    /**
     * @var string $viewStatus
     */
    protected $viewStatus;

    /**
     * @var string $className
     */
    protected $className;


    /**
     * @param string $className
     */
    public function setClassName($className = null)
    {
        $this->className = $className;
    }

    /**
     * @param \Symfony\Component\HttpFoundation\Session\Session $session
     * @return void
     * @internal param $locale
     */
    public function setLocaleBySession(Session $session)
    {
        $this->setLocale($session->get('_locale'));

        $viewStatus = $session->get('_viewStatus') ? $session->get('_viewStatus') : Page::STATUS_PUBLISHED;

        $this->setViewStatus($viewStatus);
    }

    /**
     * @param $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    /**
     * @param $viewStatus
     */
    public function setViewStatus($viewStatus)
    {
        $this->viewStatus = $viewStatus;
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

        /** @var $route ContentRoute */
        $route = $this->findOneBy($parameters['route_params']);


        if (!$route) {
            throw new RouteNotFoundException("No route found for name '$name'");
        }

        $this->initializeContentRoute($route);

        return $route;
    }

    public function getRouteCollectionForRequest(Request $request)
    {
        $url = $request->getPathInfo();

        return $this->findManyByUrl($url);
    }

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
        $collection = new RouteCollection();

        /** @var $connection \Doctrine\DBAL\Connection */
        $connection = $this->getEntityManager()->getConnection();
        try {
            $connection->connect();
        } catch (\Exception $e) {
            return $collection;
        }

        if (!$connection->isConnected()) {
            return $collection;
        }

        $searchUrl = (substr($url, -1) != '/') ? $url . '/' : $url;


        $params = array('path' => $searchUrl);

        if ($this->locale) {
            $params['locale'] = $this->locale;
        }

        $contentRoutes = $this->findBy($params);


        foreach ($contentRoutes as $key => $contentRoute) {

            $content = $this->getRouteContent($contentRoute);

            if ($this->viewStatus == Page::STATUS_DRAFT && ($content instanceof ResourceVersionInterface)) {
                continue;
            } elseif ($this->viewStatus == Page::STATUS_PUBLISHED && ($content instanceof VersionableInterface)) {
                continue;
            }

            $contentRoute->initializeRoute($content);

            $contentRoute->setPattern($url);

            $collection->add(
                self::ROUTE_GENERATE_DUMMY_NAME . preg_replace('/[^a-z0-9A-Z_.]/', '_', $key),
                $contentRoute
            );
        }

        return $collection;
    }


    /**
     * @param ContentRoute $contentRoute
     */
    protected function initializeContentRoute(ContentRoute &$contentRoute)
    {
        $content = $this->getRouteContent($contentRoute);

        $contentRoute->initializeRoute($content);
    }

    /**
     * @param  ContentRoute $contentRoute
     * @return object
     */
    protected function getRouteContent(ContentRoute $contentRoute)
    {
        $repository = $this->getEntityManager()->getRepository($contentRoute->getClassType());

        return $repository->find($contentRoute->getObjectId());
    }

}
