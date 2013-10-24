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
 * Class AdminSettings
 * @package Networking\InitCmsBundle\Entity
 */
class AdminSettings implements AdminSettingsInterface
{
    /**
     * @var array
     */
    public $settings = array();

    /**
     * @param $settings
     */
    public function setSettings($settings)
    {
        $this->settings = $settings;
    }

    /**
     * @return array
     */
    public function getSettings()
    {
        return $this->settings;
    }

    /**
     * @param $key
     * @param $value
     */
    public function setSetting($key, $value)
    {
        $this->settings[$key] = $value;
    }

    /**
     * @param $key
     * @return bool
     */
    public function getSetting($key)
    {
        if (array_key_exists($key, $this->settings)) {
            return $this->settings[$key];
        } else {
            return false;
        }
    }
}
