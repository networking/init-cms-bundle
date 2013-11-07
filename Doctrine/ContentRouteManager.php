<?php
/**
 * This file is part of the init_cms_sandbox package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Doctrine;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Networking\InitCmsBundle\Doctrine\Extensions\Versionable\ResourceVersionInterface;
use Networking\InitCmsBundle\Doctrine\Extensions\Versionable\VersionableInterface;
use Networking\InitCmsBundle\Model\ContentRouteManager as BaseContentRouteManager;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Routing\RouteCollection;
use Networking\InitCmsBundle\Model\ContentRouteInterface;

/**
 * Class ContentRouteManager
 * @package Networking\InitCmsBundle\Doctrine
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class ContentRouteManager extends BaseContentRouteManager
{
    /**
     * @var \Doctrine\Common\Persistence\ObjectManager
     */
    protected $objectManager;
    /**
     * @var
     */
    protected $class;

    /**
     * @var Session
     */
    protected $session;
    /**
     * @var ObjectRepository
     */
    protected $repository;

    public function __construct(ObjectManager $om, $class, Session $session)
    {
        $this->objectManager = $om;
        $this->repository = $om->getRepository($class);

        $metadata = $om->getClassMetadata($class);
        $this->class = $metadata->getName();
        $this->session = $session;
    }

    /**
     * {@inheritDoc}
     */
    public function getClass()
    {
        return $this->class;
    }

    /**
     * @param $criteria
     * @return mixed
     */
    public function findContentRouteBy(array $criteria)
    {
        return $this->repository->findOneBy($criteria);
    }

    public function findContentByContentRoute(ContentRouteInterface $contentRoute)
    {

        $repository = $this->objectManager->getRepository($contentRoute->getClassType());

        return $repository->find($contentRoute->getObjectId());
    }

    /**
     * @param $criteria
     * @return int
     */
    public function findContentRoute($criteria)
    {
        return $this->repository->find($criteria);
    }

    /**
     * {@inheritDoc}
     */
    public function findContentRoutesBy($url)
    {
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

        $searchUrl = (substr($url, -1) != '/') ? $url . '/' : $url;


        $params = array('path' => $searchUrl);


        try {
            $contentRoutes = $this->repository->findBy($params);
        } catch (\Doctrine\DBAL\DBALException $e) {

            return $collection;
        }

        if(empty($contentRoutes)){
            return $collection;
        }

        $tempContentRoutes = array_filter($contentRoutes, array($this, 'filterByLocale'));

        if(empty($tempContentRoutes)){
            $tempContentRoutes = $contentRoutes;
        }

        foreach ($tempContentRoutes as $key => $contentRoute) {


            /** @var \Networking\InitCmsBundle\Model\ContentRouteInterface $contentRoute */
            $content = $this->getRouteContent($contentRoute);

            $viewStatus = $this->session->get('_viewStatus') ? $this->session->get(
                '_viewStatus'
            ) : VersionableInterface::STATUS_PUBLISHED;

            if ($viewStatus == VersionableInterface::STATUS_DRAFT && ($content instanceof ResourceVersionInterface)) {
                continue;
            } elseif ($viewStatus == VersionableInterface::STATUS_PUBLISHED && ($content instanceof VersionableInterface)) {
                continue;
            }

            $this->session->set('_locale', $contentRoute->getLocale());

            $contentRoute->initializeRoute($content);

            $contentRoute->setPath($url);

            $collection->add(
                self::ROUTE_GENERATE_DUMMY_NAME . preg_replace('/[^a-z0-9A-Z_.]/', '_', $key),
                $contentRoute
            );
        }

        return $collection;
    }

    protected function filterByLocale($var)
    {
        return $var->getLocale() == $this->session->get('_locale');
    }
}
