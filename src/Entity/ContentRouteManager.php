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

namespace Networking\InitCmsBundle\Entity;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\Persistence\ObjectRepository;
use Networking\InitCmsBundle\Component\Routing\Route;
use Networking\InitCmsBundle\Doctrine\Extensions\Versionable\ResourceVersionInterface;
use Networking\InitCmsBundle\Doctrine\Extensions\Versionable\VersionableInterface;
use Networking\InitCmsBundle\Helper\PageHelper;
use Networking\InitCmsBundle\Model\ContentRouteInterface;
use Networking\InitCmsBundle\Model\ContentRouteManagerInterface;
use Networking\InitCmsBundle\Model\PageInterface;
use Networking\InitCmsBundle\Model\PageSnapshotInterface;
use Symfony\Bridge\Twig\Attribute\Template;
use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Cmf\Component\Routing\RouteProviderInterface;
use Symfony\Cmf\Component\Routing\RouteReferrersReadInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Exception\RouteNotFoundException;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class ContentRouteManager.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class ContentRouteManager implements ContentRouteManagerInterface, RouteProviderInterface
{
    /**
     * @var EntityManagerInterface
     */
    protected $objectManager;
    /**
     * @var Request
     */
    protected $request;
    /**
     * @var ObjectRepository
     */
    protected $repository;

    /**
     * @var string
     */
    protected $className;

    /**
     * @var string
     */
    protected $class;

    /**
     * ContentRouteManager constructor.
     */
    public function __construct(EntityManagerInterface $om, $class)
    {
        $this->objectManager = $om;
        $this->setClassName($class);
    }

    public function setClassName(?string $className): void
    {
        $this->className = $className;
    }

    public function getClassName(): string
    {
        return $this->className;
    }

    public function getRouteByName($name): Route
    {
        throw new RouteNotFoundException("No route found for name '$name'");
    }

    public function getRoutesByNames($names = null): array
    {
        return [];
    }

    public function initializeContentRoute(ContentRouteInterface &$contentRoute
    ): void {
        $content = $this->getRouteContent($contentRoute);

        $contentRoute->setContent($content);
    }

    public function getRouteContent(ContentRouteInterface $contentRoute
    ): ?RouteReferrersReadInterface {
        return $this->findContentByContentRoute($contentRoute);
    }

    public function getClass(): string
    {
        return $this->class;
    }

    public function setClass(string $class): self
    {
        $this->class = $class;

        return $this;
    }

    /**
     * @return Route
     */
    public static function generateRoute(
        ContentRouteInterface $contentRoute,
        string $path,
        PageInterface|PageSnapshotInterface|string $content,
        bool $addLocale = true
    ): RouteObjectInterface {
        $template = new Template($contentRoute->getTemplate());

        if (!$path && $content instanceof PageInterface) {
            $path = PageHelper::getPageRoutePath($content->getPath());
            $contentRoute->setPath($path);
        }

        $defaults = [
            'route_params' => '',
            Route::LOCALE => $contentRoute->getLocale(),
            RouteObjectInterface::TEMPLATE_NAME => $template,
            RouteObjectInterface::CONTENT_OBJECT => $content,
        ];

        if ($controller = $contentRoute->getController()) {
            $defaults[RouteObjectInterface::CONTROLLER_NAME] = $controller;
        }
        if (self::hasLocaleUrl() && $addLocale) {
            $locale = substr($contentRoute->getLocale(), 0, 2);
            $path = self::stripLocale($path, $locale);
            $path = '/'.$locale.$path;
        }

        return new Route($path, $defaults);
    }

    public static function hasLocaleUrl(): bool
    {
        return !getenv('ALLOW_LOCALE_COOKIE', true)
            && !getenv(
                'SINGLE_LANGUAGE',
                true
            );
    }

    protected static function stripLocale($url, $locale): string
    {
        if (!self::hasLocaleUrl()) {
            return $url;
        }

        $locale = substr((string) $locale, 0, 2);
        $parts = explode('/', (string) $url);

        if (count($parts) < 2) {
            return $url;
        }

        $urlLocale = $parts[1];

        if ($urlLocale === $locale) {
            return substr((string) $url, 3);
        }

        return $url;
    }

    /**
     * @return mixed|object|null
     * @deprecated will be removed
     */
    public function findContentRouteBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    public function findContentByContentRoute(
        ContentRouteInterface $contentRoute
    ): ?RouteReferrersReadInterface {
        $repository = $this->objectManager->getRepository(
            $contentRoute->getClassType()
        );

        return $repository->find($contentRoute->getObjectId());
    }

    public function getRouteCollectionForRequest(Request $request
    ): RouteCollection {
        $url = $request->getPathInfo();

        $collection = new RouteCollection();

        $searchUrl = (!str_ends_with($url, '/')) ? $url.'/' : $url;

        $searchUrl = self::stripLocale($searchUrl, $request->getLocale());

        $params = ['path' => $searchUrl];

        try {
            $contentRoutes = $this->objectManager->getRepository(
                $this->className
            )->findBy($params);
        } catch (\UnexpectedValueException) {
            return $collection;
        }

        if (empty($contentRoutes)) {
            return $collection;
        }

        $filterByLocale = function (ContentRouteInterface $var) use ($request) {
            if ($request) {
                return $var->getLocale() == $request->getLocale();
            } else {
                return true;
            }
        };

        $tempContentRoutes = array_filter($contentRoutes, $filterByLocale);

        if (empty($tempContentRoutes)) {
            $tempContentRoutes = $contentRoutes;
        }

        foreach ($tempContentRoutes as $key => $contentRoute) {
            $viewStatus = ($request->hasSession()) ? $request->getSession()
                ->get('_viewStatus', VersionableInterface::STATUS_PUBLISHED)
                : VersionableInterface::STATUS_PUBLISHED;

            try {
                $test = new \ReflectionClass($contentRoute->getClassType());
            } catch (\ReflectionException) {
                continue;
            }

            if (VersionableInterface::STATUS_DRAFT == $viewStatus
                && $test->implementsInterface(ResourceVersionInterface::class)
            ) {
                continue;
            } elseif (VersionableInterface::STATUS_PUBLISHED == $viewStatus
                && $test->implementsInterface(VersionableInterface::class)
            ) {
                continue;
            }

            $content = $this->getRouteContent($contentRoute);

            if ('/' === $searchUrl) {
                $collection->add(
                    sprintf('%s/%s', $contentRoute->getLocale(), $searchUrl),
                    static::generateRoute($contentRoute, $url, $content, false)
                );
            } else {
                $collection->add(
                    sprintf('%s/%s', $contentRoute->getLocale(), $searchUrl),
                    static::generateRoute($contentRoute, $url, $content)
                );
            }
        }

        return $collection;
    }
}
