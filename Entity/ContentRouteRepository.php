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
    Symfony\Component\Routing\RequestContext,
    Symfony\Cmf\Component\Routing\RouteProviderInterface,
    Symfony\Component\HttpFoundation\Session\Session,
    Symfony\Component\Routing\RouteCollection,
    Symfony\Cmf\Component\Routing\DynamicRouter,
        Symfony\Cmf\Component\Routing\NestedMatcher\ConfigurableUrlMatcher,
    Symfony\Component\Routing\Matcher\UrlMatcherInterface,
    Symfony\Component\Routing\Generator\UrlGeneratorInterface,
    Symfony\Component\Routing\Exception\RouteNotFoundException,
    Symfony\Component\HttpFoundation\Request,
    Symfony\Component\Routing\Exception\MissingMandatoryParametersException,
    Symfony\Component\Routing\Exception\InvalidParameterException,
    Networking\InitCmsBundle\Doctrine\Extensions\Versionable\ResourceVersionInterface,
    Networking\InitCmsBundle\Doctrine\Extensions\Versionable\VersionableInterface,
    Networking\InitCmsBundle\Entity\ContentRoute,
    Networking\InitCmsBundle\Entity\Page;

/**
 * @author net working AG <info@networking.ch>
 */
class ContentRouteRepository extends EntityRepository implements RouteProviderInterface
{
    const ROUTE_GENERATE_DUMMY_NAME = 'cmf_routing_dynamic_route';

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
     * @var \Symfony\Component\Routing\RequestContext $context
     */
    protected $context;

    /**
     * @param \Symfony\Component\Routing\RequestContext $context
     * @return ContentRouteRepository
     */
    public function setContext(RequestContext $context)
    {
        $this->context = $context;

        return $this;
    }

    /**
     * @return mixed
     */
    public function getContext()
    {
        return $this->context;
    }

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

    public function generate($name, $parameters = array(), $absolute = false)
    {
        $route = $this->getRouteByName($name, $parameters);

        if (!$route) {
            throw new RouteNotFoundException(sprintf('Route "%s" does not exist.', $name));
        }
        $compiledRoute = $route->compile();

        // handle symfony 2.1 and 2.2
        // getHostnameTokens exists only since 2.2
        $hostnameTokens = null;
        if (method_exists($compiledRoute, 'getHostnameTokens')) {
            $hostnameTokens = $compiledRoute->getHostnameTokens();
        }

        return $this->doGenerate(
            $compiledRoute->getVariables(),
            $route->getDefaults(),
            $route->getRequirements(),
            $compiledRoute->getTokens(),
            $parameters,
            $name,
            $absolute,
            $hostnameTokens
        );
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

    /**
     * @throws MissingMandatoryParametersException When route has some missing mandatory parameters
     * @throws InvalidParameterException When a parameter value is not correct
     */
    protected function doGenerate($variables, $defaults, $requirements, $tokens, $parameters, $name, $absolute)
    {
        $variables = array_flip($variables);

        $originParameters = $parameters;
        $parameters = array_replace($this->context->getParameters(), $parameters);
        $tparams = array_replace($defaults, $parameters);

        // all params must be given
        if ($diff = array_diff_key($variables, $tparams)) {
            throw new MissingMandatoryParametersException(sprintf(
                'The "%s" route has some missing mandatory parameters ("%s").',
                $name,
                implode('", "', array_keys($diff))
            ));
        }

        $url = '';
        $optional = true;
        foreach ($tokens as $token) {
            if ('variable' === $token[0]) {
                if (false === $optional || !array_key_exists(
                    $token[3],
                    $defaults
                ) || (isset($parameters[$token[3]]) && (string)$parameters[$token[3]] != (string)$defaults[$token[3]])
                ) {
                    if (!$isEmpty = in_array($tparams[$token[3]], array(null, '', false), true)) {
                        // check requirement
                        if ($tparams[$token[3]] && !preg_match('#^' . $token[2] . '$#', $tparams[$token[3]])) {
                            $message = sprintf(
                                'Parameter "%s" for route "%s" must match "%s" ("%s" given).',
                                $token[3],
                                $name,
                                $token[2],
                                $tparams[$token[3]]
                            );
                            if ($this->strictRequirements) {
                                throw new InvalidParameterException($message);
                            }

                            if ($this->logger) {
                                $this->logger->err($message);
                            }

                            return null;
                        }
                    }

                    if (!$isEmpty || !$optional) {
                        $url = $token[1] . $tparams[$token[3]] . $url;
                    }

                    $optional = false;
                }
            } elseif ('text' === $token[0]) {
                $url = $token[1] . $url;
                $optional = false;
            }
        }

        if ('' === $url) {
            $url = '/';
        }

        // do not encode the contexts base url as it is already encoded (see Symfony\Component\HttpFoundation\Request)
        $url = $this->context->getBaseUrl() . strtr(rawurlencode($url), $this->decodedChars);

        // the path segments "." and ".." are interpreted as relative reference when resolving a URI; see http://tools.ietf.org/html/rfc3986#section-3.3
        // so we need to encode them as they are not used for this purpose here
        // otherwise we would generate a URI that, when followed by a user agent (e.g. browser), does not match this route
        $url = strtr($url, array('/../' => '/%2E%2E/', '/./' => '/%2E/'));
        if ('/..' === substr($url, -3)) {
            $url = substr($url, 0, -2) . '%2E%2E';
        } elseif ('/.' === substr($url, -2)) {
            $url = substr($url, 0, -1) . '%2E';
        }

        // add a query string if needed
        $extra = array_diff_key($originParameters, $variables, $defaults);
        if ($extra && $query = http_build_query($extra, '', '&')) {
            $url .= '?' . $query;
        }

        if ($this->context->getHost()) {
            $scheme = $this->context->getScheme();
            if (isset($requirements['_scheme']) && ($req = strtolower($requirements['_scheme'])) && $scheme != $req) {
                $absolute = true;
                $scheme = $req;
            }

            if ($absolute) {
                $port = '';
                if ('http' === $scheme && 80 != $this->context->getHttpPort()) {
                    $port = ':' . $this->context->getHttpPort();
                } elseif ('https' === $scheme && 443 != $this->context->getHttpsPort()) {
                    $port = ':' . $this->context->getHttpsPort();
                }

                $url = $scheme . '://' . $this->context->getHost() . $port . $url;
            }
        }

        return $url;
    }
}
