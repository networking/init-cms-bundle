<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Entity;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
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
     * @var \Doctrine\Common\Persistence\ObjectManager
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
     * @param ObjectManager $om
     * @param $class
     */
    public function __construct(ObjectManager $om, $class)
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
     * @param ContentRouteInterface $contentRoute
     *
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
    public function getRouteCollectionForRequest(Request $request)
    {
        $url = $request->getPathInfo();

        $collection = new RouteCollection();

        /** @var $connection \Doctrine\DBAL\Connection */
        $connection = $this->objectManager->getConnection();

        try {
            $connection->connect();
        } catch (\Exception $e) {
            return $collection;
        }

        if (!$connection->isConnected()) {
            return $collection;
        }

        $searchUrl = (substr($url, -1) != '/') ? $url.'/' : $url;

        $searchUrl = self::stripLocale($searchUrl, $request->getLocale());

        $params = ['path' => $searchUrl];

        try {
            $contentRoutes = $this->repository->findBy($params);
        } catch (\UnexpectedValueException $e) {
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
            } catch (\ReflectionException $e) {
                continue;
            }

            if ($viewStatus == VersionableInterface::STATUS_DRAFT
                && ($test->implementsInterface('Networking\InitCmsBundle\Doctrine\Extensions\Versionable\ResourceVersionInterface'))
            ) {
                continue;
            } elseif ($viewStatus == VersionableInterface::STATUS_PUBLISHED
                && ($test->implementsInterface('Networking\InitCmsBundle\Doctrine\Extensions\Versionable\VersionableInterface'))
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
