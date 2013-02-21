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
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class AdminSettings
{
    protected $settings = array();

    public function setSettings($settings)
    {
        $this->settings = $settings;
    }

    public function getSettings()
    {
        return $this->settings;
    }

    public function setSetting($key, $value)
    {
        $this->settings[$key] = $value;
    }

    public function getSetting($key)
    {
        if (array_key_exists($key, $this->settings)) {
            return $this->settings[$key];
        } else {
            return false;
        }
    }
}
