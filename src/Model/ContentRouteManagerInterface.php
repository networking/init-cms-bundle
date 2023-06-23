<?php

declare(strict_types=1);

/**
 * This file is part of the init_cms_sandbox package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Model;

/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
interface ContentRouteManagerInterface
{
    public const ROUTE_GENERATE_DUMMY_NAME = 'networking_init_dynamic_route';

    /**
     * @param string $className
     */
    public function setClassName($className = null);

    /**
     * @return string
     */
    public function getClassName();

    /**
     * @param ContentRouteInterface $contentRoute
     */
    public function initializeContentRoute(ContentRouteInterface &$contentRoute);

    /**
     * @param ContentRouteInterface $contentRoute
     *
     * @return object
     */
    public function getRouteContent(ContentRouteInterface $contentRoute);

    /**
     * @param $criteria
     *
     * @return mixed
     */
    public function findContentRouteBy(array $criteria);

    /**
     * @param $contentRoute
     *
     * @return mixed
     */
    public function findContentByContentRoute(ContentRouteInterface $contentRoute);
}
