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

use Symfony\Cmf\Component\Routing\RouteReferrersReadInterface;

/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
interface ContentRouteManagerInterface
{
    public const ROUTE_GENERATE_DUMMY_NAME = 'networking_init_dynamic_route';

    /**
     * @param string $className
     */
    public function setClassName(?string $className);

    /**
     * @return string
     */
    public function getClassName(): string;

    /**
     * @param ContentRouteInterface $contentRoute
     */
    public function initializeContentRoute(ContentRouteInterface &$contentRoute): void;

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
    public function findContentByContentRoute(ContentRouteInterface $contentRoute): ?RouteReferrersReadInterface;
}
