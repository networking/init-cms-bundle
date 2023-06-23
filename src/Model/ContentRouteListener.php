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
abstract class ContentRouteListener implements ContentRouteListenerInterface
{
    /**
     * @var array
     */
    protected $templates;

    public function __construct(array $templates)
    {
        $this->templates = $templates;
    }
}
