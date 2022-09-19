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
abstract class BaseUser extends SonataBaseUser implements UserInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var \Networking\InitCmsBundle\Model\AdminSettings
     */
    protected $adminSettings;

    /**
     * @var \DateTime
     */
    protected $lastActivity;

    protected $twoStepVerificationCode;

    /**
     * Hook on pre-persist operations.
     */
    public function prePersist(): void
    {
        $this->createdAt = new \DateTime();
        $this->updatedAt = new \DateTime();
    }

    /**
     * Hook on pre-update operations.
     */
    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }

    /**
     * Get id.
     *
     * @return int $id
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
     *
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
        } else {
            $this->adminSettings = clone $this->adminSettings;
        }

        $this->adminSettings->setSetting($key, $value);
    }

    /**
     * @return mixed
     */
    public function getTwoStepVerificationCode()
    {
        return $this->twoStepVerificationCode;
    }

    /**
     * @param mixed $twoStepVerificationCode
     *
     * @return BaseUser
     */
    public function setTwoStepVerificationCode($twoStepVerificationCode)
    {
        $this->twoStepVerificationCode = $twoStepVerificationCode;

        return $this;
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
