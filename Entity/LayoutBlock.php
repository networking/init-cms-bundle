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

use Doctrine\ORM\Mapping as ORM,
    Networking\InitCmsBundle\Entity\Page,
    Networking\InitCmsBundle\Entity\ContentInterface,
    Doctrine\Common\Collections\ArrayCollection;

/**
 * Networking\InitCmsBundle\Entity\LayoutBlock
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="layout_block")
 * @ORM\Entity(repositoryClass="Networking\InitCmsBundle\Entity\LayoutBlockRepository")
 *
 * @author net working AG <info@networking.ch>
 */
class LayoutBlock implements ContentInterface
{
    /**
     * @var integer $id
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string $name
     *
     * @ORM\Column(name="name", type="string", length=255, nullable=true)
     */
    protected $name;

    /**
     * @var string $zone
     *
     * @ORM\Column(name="zone", type="string")
     */
    protected $zone;

    /**
     * @var Page $page
     *
     * @ORM\ManyToOne(targetEntity="Page", inversedBy="layoutBlock",cascade={"persist", "detach"})
     * @ORM\JoinColumn(name="page_id", referencedColumnName="id", onDelete="CASCADE")
     */
    protected $page;

    /**
     * @var string $classType
     *
     * @ORM\Column(name="class_type", type="string")
     */
    protected $classType;

    /**
     * @var int $objectId
     *
     * @ORM\Column(name="object_id", type="integer", nullable=true)
     */
    protected $objectId;

    /**
     * @var boolean $isActive
     *
     * @ORM\Column(name="is_active", type="boolean")
     */
    protected $isActive = true;

    /**
     * @var integer $sortOrder
     *
     * @ORM\Column(name="sort_order", type="integer", nullable=true)
     */
    protected $sortOrder;

    /**
     * @var \DateTime $createdAt
     *
     * @ORM\Column(name="created_at", type="datetime")
     */
    protected $createdAt;

    /**
     * @var \DateTime $updatedAt
     *
     * @ORM\Column(name="updated_at", type="datetime")
     */
    protected $updatedAt;

    /**
     * @var array $content
     */
    protected $content = array();

    /**
     * @var string $oldClassType
     */
    protected $origClassType;

    /**
     * @var boolean $isSnapshot
     */
    protected $isSnapshot = false;

    /**
     * @var ArrayCollection $snapshotContent
     */
    protected $snapshotContent;

    /**
     *
     */
    public function __construct()
    {
        $this->snapshotContent = new ArrayCollection();
    }

    /**
     *
     */
    public function __clone()
    {
        $this->id = null;
        $this->page = null;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->getContentTypeName();
    }

    /**
     * @ORM\PrePersist
     */
    public function onPrePersist()
    {
        $this->createdAt = $this->updatedAt = new \DateTime("now");
    }

    /**
     * Hook on pre-update operations
     * @ORM\PreUpdate
     */
    public function onPreUpdate()
    {
        $this->updatedAt = new \DateTime('now');
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name
     *
     * @param  string $name
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set zone
     *
     * @param  string $zone
     * @return $this
     */
    public function setZone($zone)
    {
        $this->zone = $zone;

        return $this;
    }

    /**
     * Get zone
     *
     * @return string
     */
    public function getZone()
    {
        return $this->zone;
    }

    /**
     * Set page
     *
     * @param  Page $page
     * @return $this
     */
    public function setPage(Page $page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Get conversation
     *
     * @return Page
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Get conversation
     *
     * @return Page
     */
    public function getPageId()
    {
        return $this->page->getId();
    }

    /**
     * @param  string $classType
     * @return $this
     */
    public function setClassType($classType)
    {
        if ($classType != $this->classType) {
            $this->origClassType = $this->classType;
        }
        $this->classType = $classType;

        return $this;
    }

    /**
     * @return string
     */
    public function getClassType()
    {
        return $this->classType;
    }

    /**
     * @return string
     */
    public function getOrigClassType()
    {
        return $this->origClassType;
    }

    /**
     * @param  int $objectId
     * @return $this
     */
    public function setObjectId($objectId)
    {
        $this->objectId = $objectId;

        return $this;
    }

    /**
     * @return int
     */
    public function getObjectId()
    {
        return $this->objectId;
    }

    /**
     * Set isActive
     *
     * @param  boolean $active
     * @return $this
     */
    public function setIsActive($active)
    {
        $active = $active ? true : false;
        $this->isActive = $active;

        return $this;
    }

    /**
     * Get isActive
     *
     * @return boolean
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Alias for getIsActive
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->getIsActive();
    }

    /**
     * Set createdAt
     *
     * @return $this
     */
    public function setCreatedAt()
    {
        $this->createdAt = new \DateTime();

        return $this;
    }

    /**
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt
     *
     * @param  \DateTime $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param  int $sortOrder
     * @return $this
     */
    public function setSortOrder($sortOrder)
    {
        $this->sortOrder = $sortOrder;

        return $this;
    }

    /**
     * @return int
     */
    public function getSortOrder()
    {
        return $this->sortOrder;
    }

    /**
     * @return array
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param $content
     * @param $key
     * @return $this
     */
    public function setContent($content, $key = null)
    {
        if (!is_array($content) || !is_null($key)) {
            $this->content[$key] = $content;

            return $this;
        }

        foreach ($content as $key => $data) {
            $this->content[$key] = $data;
        }

        return $this;

    }

    /**
     * @param boolean $isSnapshot
     * @return \Networking\InitCmsBundle\Entity\LayoutBlock
     */
    public function setIsSnapshot($isSnapshot)
    {
        $this->isSnapshot = $isSnapshot;

        return $this;
    }

    /**
     * @param boolean $isSnapshot
     * @return \Networking\InitCmsBundle\Entity\LayoutBlock
     */
    public function setNoAutoDraft($isSnapshot)
    {
        $this->isSnapshot = $isSnapshot;

        return $this;
    }

    /**
     * @return boolean
     */
    public function getIsSnapshot()
    {
        return $this->isSnapshot;
    }

    /**
     * @return boolean
     */
    public function getSetNoAutoDraft()
    {
        return $this->isSnapshot;
    }

    /**
     * @param array $snapshotContent
     * @return \Networking\InitCmsBundle\Entity\LayoutBlock
     */
    public function setSnapshotContent($snapshotContent)
    {
        if (!is_array($snapshotContent)) {
            $snapshotContent = array($snapshotContent);
        }
        $this->snapshotContent = new ArrayCollection($snapshotContent);

        return $this;
    }

    /**
     * @return array
     */
    public function getSnapshotContent()
    {
        return $this->snapshotContent[0];
    }

    /**
     * @param $snapshotContent
     * @return void
     * @internal param $content
     */
    public function takeSnapshot($snapshotContent)
    {

        $this->setNoAutoDraft = true;
        $this->setSnapshotContent($snapshotContent);
    }

    /**
     * @param array $params
     * @return array|bool
     */
    public function getTemplateOptions($params = array())
    {
        return false;
    }

    /**
     * @return array|bool
     */
    public static function getFieldDefinition()
    {
        return false;
    }

    /**
     * @return array|bool
     */
    public function getAdminContent()
    {
        return false;
    }

    /**
     * @return string
     */
    public function getContentTypeName()
    {
        return 'Layout Content Block';
    }

    public function import(LayoutBlock $object)
    {
        foreach (get_object_vars($object) as $key => $value) {
            if($key != 'id'){
                $this->$key = $value;
            }
        }
    }
}
