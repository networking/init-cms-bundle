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

use Symfony\Cmf\Component\Routing\RouteProviderInterface;
use Symfony\Component\HttpFoundation\Request;


/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
interface ContentRouteManagerInterface extends RouteProviderInterface
{
    const ROUTE_GENERATE_DUMMY_NAME = 'networking_init_dynamic_route';

    /**
     * @param string $className
     */
    public function setClassName($className = null);


    /**
     * @param ContentRouteInterface $contentRoute
     */
    public function initializeContentRoute(ContentRouteInterface &$contentRoute);

    /**
     * @param  ContentRouteInterface $contentRoute
     * @return object
     */
    public function getRouteContent(ContentRouteInterface $contentRoute);

    /**
     * @param $criteria
     * @return mixed
     */
    public function findContentRoutesBy($criteria);

    /**
     * @param $criteria
     * @return mixed
     */
    public function findContentRouteBy(array $criteria);

    /**
     * @param $criteria
     * @return int
     */
    public function findContentRoute($criteria);

    /**
     * @param $contentRoute
     * @return mixed
     */
    public function findContentByContentRoute(ContentRouteInterface $contentRoute);

}
