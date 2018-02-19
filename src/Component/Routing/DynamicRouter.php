<?php
/**
 * Created by PhpStorm.
 * User: yorkie
 * Date: 11.01.18
 * Time: 09:57
 */

namespace Networking\InitCmsBundle\Component\Routing;

use Symfony\Cmf\Component\Routing\ContentAwareGenerator;
use Symfony\Cmf\Component\Routing\DynamicRouter as baseRouter;
use Symfony\Cmf\Component\Routing\NestedMatcher\NestedMatcher;
use Symfony\Cmf\Component\Routing\RouteProviderInterface;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Routing\RequestContext;

class DynamicRouter extends baseRouter
{
    public function __construct(
        RequestContext $context, NestedMatcher $matcher, ContentAwareGenerator $generator, string $uriFilterRegexp = '', EventDispatcherInterface $eventDispatcher = null, RouteProviderInterface $provider = null)
    {
        parent::__construct($context, $matcher, $generator, $uriFilterRegexp, $eventDispatcher, $provider);
    }
}