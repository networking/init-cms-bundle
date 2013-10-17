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
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 *
 * @package Networking\InitCmsBundle\Model
 */
interface AdminSettingsInterface
{

    /**
     * @param $settings
     */
    public function setSettings($settings);

    /**
     * @return array
     */
    public function getSettings();

    /**
     * @param $key
     * @param $value
     */
    public function setSetting($key, $value);

    /**
     * @param $key
     * @return bool
     */
    public function getSetting($key);
}
