<?php
/**
 * This file is part of the <name> project.
 *
 * (c) <yourname> <youremail>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Entity;

use Doctrine\ORM\Mapping as ORM,
    Sonata\UserBundle\Entity\BaseUser as BaseUser,
    Networking\InitCmsBundle\Entity\AdminSettings;

/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 *
 * Networking\InitCmsBundle\Entity\Group
 *
 * @ORM\Table(name="fos_user_user")
 * @ORM\Entity(repositoryClass="Networking\InitCmsBundle\Entity\UserRepository")
 */
class User extends BaseUser
{

    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     *
     */
    protected $id;

    /**
     * @var $adminSettings \Networking\InitCmsBundle\Entity\AdminSettings
     *
     * @ORM\Column(name="admin_settings", type="object", nullable=true)
     */
    protected $adminSettings;

    /**
     * @var $lastActivity \DateTime
     */
    protected $lastActivity;

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