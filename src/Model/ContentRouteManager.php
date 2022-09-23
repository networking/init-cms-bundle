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
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Cmf\Component\Routing\RouteProviderInterface;

/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class ContentRouteManager implements ContentRouteManagerInterface, RouteProviderInterface
{
    /**
     * @var string
     */
    protected $className;

    /**
     * @var string
     */
    protected $class;

    protected $allowLocaleCookie;
    
    protected $singleLanauge;

    /**
     * {@inheritdoc}
     */
    public function setClassName($className = null)
    {
        $this->className = $className;
    }

    /**
     * {@inheritdoc}
     */
    public function getClassName()
    {
        return $this->className;
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteByName($name)
    {
        throw new RouteNotFoundException("No route found for name '$name'");
    }

    /**
     * {@inheritdoc}
     */
    public function getRoutesByNames($names)
    {
        return new RouteCollection();
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
     * @param ContentRouteInterface $contentRoute
     *
     * @return object
     */
    public function getRouteContent(ContentRouteInterface $contentRoute)
    {
        return $this->findContentByContentRoute($contentRoute);
    }

    /**
     * @return string
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param string $class
     *
     * @return ContentRouteManager
     */
    public function setClass(string $class)
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @param ContentRouteInterface $contentRoute
     * @param $path
     * @param $content
     * @param $addLocale
     *
     * @return Route
     */
    public static function generateRoute(ContentRouteInterface $contentRoute, $path, $content, $addLocale = true)
    {
        $template = new Template(['template' => $contentRoute->getTemplate()]);

        $defaults = [
            'route_params' => '',
            Route::LOCALE => $contentRoute->getLocale(),
            RouteObjectInterface::TEMPLATE_NAME => $template,
            RouteObjectInterface::CONTENT_OBJECT => $content,
        ];

        if($controller = $contentRoute->getController()){
            $defaults[RouteObjectInterface::CONTROLLER_NAME] = $controller;
        }

        if (self::hasLocaleUrl()  && $addLocale) {
            $locale = substr($contentRoute->getLocale(), 0, 2);
            $path = self::stripLocale($path, $locale);
            $path = '/'.$locale.$path;
        }

        return new Route($path, $defaults);
    }

	/**
	 * @return bool
	 */
    public static function hasLocaleUrl()
	{
		return (!getenv('ALLOW_LOCALE_COOKIE', true) && !getenv('SINGLE_LANGUAGE', true));
	}

    /**
     * @param $url
     * @param $locale
     *
     * @return bool|string
     */
    protected static function stripLocale($url, $locale)
    {
        if (!self::hasLocaleUrl()) {
            return $url;
        }

        $locale = substr($locale, 0, 2);
        $parts = explode('/', $url);

        if(count($parts) < 2){
        	return $url;
        }

        $urlLocale = $parts[1];

        if ($urlLocale === $locale) {
            return substr($url, 3);
        }

        return $url;
    }
}
