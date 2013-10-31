<?php
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
 * Class UserInterface
 * @package Networking\InitCmsBundle\Model
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
interface UserInterface extends \Sonata\UserBundle\Model\UserInterface
{
    /**
     * @return AdminSettings
     */
    public function getAdminSettings();

    /**
     * @param $key
     * @return mixed
     */
    public function getAdminSetting($key);

    /**
     * @param $key
     * @param $value
     */
    public function setAdminSetting($key, $value);

    /**
     * @return string
     */
    public function getHash();

    /**
     * @param \DateTime $lastActivity
     */
    public function setLastActivity($lastActivity);

    /**
     * @return \DateTime
     */
    public function getLastActivity();
}