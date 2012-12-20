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

use Doctrine\ORM\EntityRepository;
use Symfony\Cmf\Component\Routing\RouteRepositoryInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Cmf\Component\Routing\DynamicRouter;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Networking\InitCmsBundle\Doctrine\Extensions\Versionable\ResourceVersionInterface;
use Networking\InitCmsBundle\Doctrine\Extensions\Versionable\VersionableInterface;
use Networking\InitCmsBundle\Entity\ContentRoute;


class ContentRouteRepository extends EntityRepository implements RouteRepositoryInterface
{
    const STATUS_DRAFT = 'draft';

    const STATUS_PUBLISHED = 'published';
    /**
     * @var $locale
     */
    protected $locale;

    protected $viewStatus;

    /**
     * @var $className
     */
    protected $className;

    /**
     * @param $className
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

        $viewStatus = $session->get('_viewStatus') ? $session->get('_viewStatus') : self::STATUS_PUBLISHED;

        $this->setViewStatus($viewStatus);
    }

    /**
     * @param $locale
     */
    public function setLocale($locale)
    {
        $this->locale = $locale;
    }

    public function setViewStatus($viewStatus)
    {
        $this->viewStatus = $viewStatus;
    }

    /**
     * {@inheritDoc}
     */
    public function getRouteByName($name, $parameters = array())
    {
        $dynamicRouteName = DynamicRouter::ROUTE_GENERATE_DUMMY_NAME;
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

        if (!$connection->isConnected()) return $collection;

        $searchUrl = (substr($url, -1) != '/') ? $url . '/' : $url;


        $params = array('path' => $searchUrl);

        if ($this->locale) {
            $params['locale'] = $this->locale;
        }

        $contentRoutes = $this->findBy($params);


        foreach ($contentRoutes as $key => $contentRoute) {

            $content = $this->getRouteContent($contentRoute);

            if ($this->viewStatus == self::STATUS_DRAFT && ($content instanceof ResourceVersionInterface)) {
                continue;
            } elseif($this->viewStatus == self::STATUS_PUBLISHED && ($content instanceof VersionableInterface)){
                continue;
            }

            $contentRoute->initializeRoute($content);

            $contentRoute->setPattern($url);

            $collection->add(DynamicRouter::ROUTE_GENERATE_DUMMY_NAME . preg_replace('/[^a-z0-9A-Z_.]/', '_', $key), $contentRoute);
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
