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

/**
 * Dummy Route Class for creating routes on the run. Used when building navigation items
 *
 * Class CMSRoute
 * @package Networking\InitCmsBundle\Component\Routing
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class CMSRoute extends AbstractRoute
{

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
    public function getContent()
    {
        // TODO: Implement getContent() method.
    }
}
