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
use Symfony\Component\Routing\RouteCompiler;
/**
 * Dummy Route Class for creating routes on the run. Used when building navigation items.
 *
 * Class CMSRoute
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class Route extends SymfonyRoute implements RouteObjectInterface
{
    const LOCALE = '_locale';

    /**
     * @param $pattern
     * @param array $defaults
     */
    public function __construct($pattern, array $defaults = [])
    {
        $this->setPath($pattern);
        $this->setDefaults($defaults);
        $this->setOptions([
            'compiler_class' => RouteCompiler::class
        ]);
    }

    /**
     * @return mixed
     */
    public function getRouteKey(): ?string
    {
        return $this->getPath();
    }



    /**
     * Get the content document this route entry stands for. If non-null,
     * the ControllerClassMapper uses it to identify a controller and
     * the content is passed to the controller.
     *
     * If there is no specific content for this url (i.e. its an "application"
     * page), may return null.
     *
     * @return object the document or entity this route entry points to
     */
    public function getContent(): ?object
    {
        return $this->getDefault(self::CONTENT_OBJECT);
    }

    /**
     * @return mixed
     */
    public function getLocale()
    {
        return $this->getDefault(self::LOCALE);
    }
}
