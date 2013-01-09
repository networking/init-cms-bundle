<?php
/*
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Helper;

use Networking\InitCmsBundle\Entity\Page;
/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class PageHelper
{
    /**
     * @param $path
     * @return string
     */
    public static function getPageRoutePath($path)
    {
        $pathArray = explode(Page::PATH_SEPARATOR, $path);

        foreach ($pathArray as $key => $path) {
            $pathArray[$key] = preg_replace('/-(\d)+$/', '', $path);
        }
        $path = implode(Page::PATH_SEPARATOR, $pathArray);

        if (substr($path, 0, 1) != Page::PATH_SEPARATOR) {
            $path = Page::PATH_SEPARATOR . $path;
        }

        return $path;
    }
}
