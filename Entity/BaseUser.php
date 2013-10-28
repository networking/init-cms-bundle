<?php
/**
 * This file is part of the init_cms_sandbox package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
 

namespace Networking\InitCmsBundle\Entity;

use Networking\InitCmsBundle\Model\UserInterface;
use Sonata\UserBundle\Entity\BaseUser as SonataBaseUser;
use Networking\InitCmsBundle\Model\AdminSettings;

/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class BaseUser extends SonataBaseUser implements UserInterface {



    /**
     * @var integer $id
     *
     *
     */
    protected $id;

    /**
     * @var $adminSettings \Networking\InitCmsBundle\Model\AdminSettings
     *
     */
    protected $adminSettings;

    /**
     * @var $lastActivity \DateTime
     */
    protected $lastActivity;

    /**
     * Hook on pre-persist operations
     */
    public function prePersist()
    {
        $this->createdAt = new \DateTime;
        $this->updatedAt = new \DateTime;
    }

    /**
     * Hook on pre-update operations
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime;
    }

    /**
     * Get id
     *
     * @return integer $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return AdminSettings
     */
    public function getAdminSettings()
    {
        if (!$this->adminSettings) {
            $this->adminSettings = new AdminSettings();
        }

        return $this->adminSettings;
    }

    /**
     * @param $key
     * @return mixed
     */
    public function getAdminSetting($key)
    {
        if (!$this->adminSettings) {
            $this->adminSettings = new AdminSettings();
        }

        return $this->adminSettings->getSetting($key);
    }

    /**
     * @param $key
     * @param $value
     */
    public function setAdminSetting($key, $value)
    {
        if (!$this->adminSettings) {
            $this->adminSettings = new AdminSettings();
        }

        $this->adminSettings->setSetting($key, $value);
    }

    public function getHash()
    {
        return md5(strtolower(trim($this->email)));
    }

    /**
     * @param \DateTime $lastActivity
     */
    public function setLastActivity($lastActivity)
    {
        $this->updatedAt = $lastActivity;
    }

    /**
     * @return \DateTime
     */
    public function getLastActivity()
    {
        return $this->updatedAt;
    }
}
