<?php

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Entity;

/**
 * @author net working AG <info@networking.ch>
 */
interface ContentInterface
{

    public function __clone();
    /**
     * @static
     * @return array
     */
    public static function getFieldDefinition();

    /**
     * @param array $params
     * @return array
     */
    public function getTemplateOptions($params = array());

    /**
     * @return array
     */
    public function getAdminContent();
}
