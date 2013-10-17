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
 * @package Networking\InitCmsBundle\Entity
 */
interface ContentInterface
{

    /**
     * @return mixed
     */
    public function __clone();

    /**
     * @param array $params
     * @return array
     */
    public function getTemplateOptions($params = array());

    /**
     * @return array
     */
    public function getAdminContent();

    /**
     * @return string
     */
    public function getContentTypeName();
}
