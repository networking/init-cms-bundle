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
use Networking\InitCmsBundle\Doctrine\Extensions\Versionable\VersionableInterface;
use Networking\InitCmsBundle\Model\ContentRouteInterface;
use Networking\InitCmsBundle\Model\ContentRouteManager as BaseContentRouteManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\RouteCollection;

/**
 * Class ContentRouteManager.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class ContentRouteManager extends BaseContentRouteManager
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
     * ContentRouteManager constructor.
     *
     * @param $class
     */
    public function __construct(EntityManagerInterface $om, $class)
    {
        $this->objectManager = $om;
        $this->repository = $om->getRepository($class);
        $this->setClassName($class);
    }

    /**
     * @param $criteria
     *
     * @return mixed
     */
    public function findContentRouteBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    /**
     * @return object
     */
    public function findContentByContentRoute(ContentRouteInterface $contentRoute)
    {
        $repository = $this->objectManager->getRepository($contentRoute->getClassType());

        return $repository->find($contentRoute->getObjectId());
    }

    /**
     * {@inheritdoc}
     */
    public function getRouteCollectionForRequest(Request $request): RouteCollection
    {
        $url = $request->getPathInfo();

        $collection = new RouteCollection();

        /** @var $connection \Doctrine\DBAL\Connection */
        $connection = $this->objectManager->getConnection();

        try {
            $connection->connect();
        } catch (\Exception) {
            return $collection;
        }

        if (!$connection->isConnected()) {
            return $collection;
        }

        $searchUrl = (!str_ends_with($url, '/')) ? $url.'/' : $url;

        $searchUrl = self::stripLocale($searchUrl, $request->getLocale());

        $params = ['path' => $searchUrl];

        try {
            $contentRoutes = $this->repository->findBy($params);
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
            $viewStatus = ($request && $request->hasSession()) ? $request->getSession()->get('_viewStatus', VersionableInterface::STATUS_PUBLISHED) : VersionableInterface::STATUS_PUBLISHED;

                try {
                $test = new \ReflectionClass($contentRoute->getClassType());
            } catch (\ReflectionException) {
                continue;
            }

            if ($viewStatus == VersionableInterface::STATUS_DRAFT
                && ($test->implementsInterface(\Networking\InitCmsBundle\Doctrine\Extensions\Versionable\ResourceVersionInterface::class))
            ) {
                continue;
            } elseif ($viewStatus == VersionableInterface::STATUS_PUBLISHED
                && ($test->implementsInterface(\Networking\InitCmsBundle\Doctrine\Extensions\Versionable\VersionableInterface::class))
            ) {
                continue;
            }

            /** @var \Networking\InitCmsBundle\Model\ContentRouteInterface $contentRoute */
            $content = $this->getRouteContent($contentRoute);

            if ($searchUrl === '/') {
                $collection->add(
                    sprintf('%s/%s', $contentRoute->getLocale(), $searchUrl),
                    static::generateRoute($contentRoute, $url, $content, false));
            } else {
                $collection->add(
                    sprintf('%s/%s', $contentRoute->getLocale(), $searchUrl),
                    static::generateRoute($contentRoute, $url, $content));
            }
        }

        return $collection;
    }
}
