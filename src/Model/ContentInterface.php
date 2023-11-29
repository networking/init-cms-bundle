<?php

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Model;

/**
 * @author net working AG <info@networking.ch>
 *
 * Class ContentInterface
 */
interface ContentInterface
{
    /**
     * @return void
     */
    public function __clone();

    /**
     * @return int
     */
    public function getId();

    /**
     * @param array $params
     *
     * @return array
     */
    public function getTemplateOptions($params = []): array;

    /**
     * @return array
     */
    public function getAdminContent(): array;

    /**
     * @return string
     */
    public function getContentTypeName();
}
