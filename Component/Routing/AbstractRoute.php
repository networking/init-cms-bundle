<?php

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Component\Routing;

use Symfony\Cmf\Component\Routing\RouteObjectInterface;
use Symfony\Component\Routing\Route as SymfonyRoute;

/**
 * @author net working AG <info@networking.ch>
 */
abstract class AbstractRoute extends SymfonyRoute implements RouteObjectInterface
{

    /**
     * @var $content
     */
    protected $content;

    /**
     * @param $content
     * @param $pattern
     * @param array $defaults
     * @param array $requirements
     * @param array $options
     */
    public function __construct($content, $pattern, array $defaults = array(), array $requirements = array(), array $options = array())
    {
        $this->content = $content;

        $this->setPattern($pattern);
        $this->setRequirements($requirements);
        $this->setDefaults($defaults);
        $this->setOptions($options);
    }

    /**
     * @return Page|object
     */
    public function getRouteContent()
    {
        return $this->content;
    }

    /**
     * @return mixed
     */
    public function getRouteDefaults()
    {
        return $this->getDefaults();
    }

    /**
     * @return mixed
     */
    public function getRouteKey()
    {
        return $this->content->getContentRoute()->getPath();
    }
}
