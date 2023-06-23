<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Networking\InitCmsBundle\Entity;

use Networking\InitCmsBundle\Model\ContentRouteInterface;
use Networking\InitCmsBundle\Model\Page as ModelPage;

/**
 * Networking\InitCmsBundle\Entity\BasePage.
 *
 * @author net working AG <info@networking.ch>
 */
class BasePage extends ModelPage
{
    public function getContentRoute(): ContentRouteInterface
    {
        if (!$this->contentRoute) {
            $this->contentRoute = new ContentRoute();
        }

        return $this->contentRoute;
    }
}
