<?php

namespace Networking\InitCmsBundle\Entity;
/**
 * Created by JetBrains PhpStorm.
 * User: yorkie
 * Date: 05.09.12
 * Time: 14:53
 * To change this template use File | Settings | File Templates.
 */
interface ContentInterface
{

    /**
     * @static
     * @return array
     */
    public static function getFieldDefinition();

    /**
     * @return array
     */
    public function getTemplateOptions();

    /**
     * @return array
     */
    public function getAdminContent();
}
