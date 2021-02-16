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

use Doctrine\Common\Collections\ArrayCollection;

/**
 * Networking\InitCmsBundle\Entity\LayoutBlock.
 *
 * @author net working AG <info@networking.ch>
 */
class LayoutBlock implements LayoutBlockInterface
{
    /**
     * @var int
     */
    protected $id;

    /**
     * @var string
     */
    protected $name;

    /**
     * @var string
     */
    protected $zone;

    /**
     * @var PageInterface
     */
    protected $page;

    /**
     * @var string
     */
    protected $classType;

    /**
     * @var int
     */
    protected $objectId;

    /**
     * @var bool
     */
    protected $isActive = true;

    /**
     * @var int
     */
    protected $sortOrder;

    /**
     * @var \DateTime
     */
    protected $createdAt;

    /**
     * @var \DateTime
     */
    protected $updatedAt;

    /**
     * @var ContentInterface
     */
    protected $content;

    /**
     * @var string
     */
    protected $origClassType;

    /**
     * @var bool
     */
    protected $isSnapshot = false;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    protected $snapshotContent;

    public function __construct()
    {
        $this->snapshotContent = new ArrayCollection();
        $this->page = null;
    }

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

    public function prePersist()
    {
        $this->createdAt = $this->updatedAt = new \DateTime('now');
    }

    /**
     * Hook on pre-update operations.
     */
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime('now');
    }

    /**
     * Get id.
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set name.
     *
     * @param string $name
     *
     * @return $this
     */
    public function setName($name)
    {
        $this->name = $name;

        return $this;
    }

    /**
     * Get name.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Set zone.
     *
     * @param string $zone
     *
     * @return $this
     */
    public function setZone($zone)
    {
        $this->zone = $zone;

        return $this;
    }

    /**
     * Get zone.
     *
     * @return string
     */
    public function getZone()
    {
        return $this->zone;
    }

    /**
     * Set page.
     *
     * @param PageInterface $page
     *
     * @return $this
     */
    public function setPage(PageInterface $page)
    {
        $this->page = $page;

        return $this;
    }

    /**
     * Get conversation.
     *
     * @return PageInterface
     */
    public function getPage()
    {
        return $this->page;
    }

    /**
     * Get conversation.
     *
     * @return int
     */
    public function getPageId()
    {
        if(!$this->page){
            return null;
        }
        return $this->page->getId();
    }

    /**
     * @param string $classType
     *
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
     * @param int $objectId
     *
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
     * Set isActive.
     *
     * @param bool $active
     *
     * @return $this
     */
    public function setIsActive($active)
    {
        $active = $active ? true : false;
        $this->isActive = $active;

        return $this;
    }

    /**
     * Get isActive.
     *
     * @return bool
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * Alias for getIsActive.
     *
     * @return bool
     */
    public function isActive()
    {
        return $this->getIsActive();
    }

    /**
     * Set createdAt.
     *
     * @return $this
     */
    public function setCreatedAt()
    {
        $this->createdAt = new \DateTime();

        return $this;
    }

    /**
     * Get createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set updatedAt.
     *
     * @param \DateTime $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }

    /**
     * Get updatedAt.
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updatedAt;
    }

    /**
     * @param int $sortOrder
     *
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
     * @return ContentInterface
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param ContentInterface $content
     *
     * @return $this
     */
    public function setContent($content)
    {
        $this->content = $content;

        return $this;
    }

    /**
     * @param bool $isSnapshot
     *
     * @return \Networking\InitCmsBundle\Entity\LayoutBlock
     */
    public function setIsSnapshot($isSnapshot)
    {
        $this->isSnapshot = $isSnapshot;

        return $this;
    }

    /**
     * @param bool $isSnapshot
     *
     * @return \Networking\InitCmsBundle\Entity\LayoutBlock
     */
    public function setNoAutoDraft($isSnapshot)
    {
        $this->isSnapshot = $isSnapshot;

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsSnapshot()
    {
        return $this->isSnapshot;
    }

    /**
     * @return bool
     */
    public function getSetNoAutoDraft()
    {
        return $this->isSnapshot;
    }

    /**
     * @param array $snapshotContent
     *
     * @return \Networking\InitCmsBundle\Entity\LayoutBlock
     */
    public function setSnapshotContent($snapshotContent)
    {
//        if (!is_array($snapshotContent)) {
//            $snapshotContent = [$snapshotContent];
//        }
        $this->snapshotContent = $snapshotContent;//new ArrayCollection($snapshotContent);

        return $this;
    }

    /**
     * @return array
     */
    public function getSnapshotContent()
    {
        return $this->snapshotContent;
    }

    /**
     * @param $snapshotContent
     *
     * @internal param $content
     */
    public function takeSnapshot($snapshotContent)
    {
        $this->setNoAutoDraft = true;
        $this->setSnapshotContent($snapshotContent);
    }

    /**
     * @param array $params
     *
     * @return array|bool
     */
    public function getTemplateOptions($params = [])
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

    public function import(LayoutBlockInterface $object)
    {
        foreach (get_object_vars($object) as $key => $value) {
            if ($key != 'id') {
                $this->$key = $value;
            }
        }
    }

    public function restoreFormPublished(LayoutBlock $published){
        $this->name = $published->getName();
        $this->zone = $published->getZone();
        $this->classType = $published->getClassType();
        $this->objectId = $published->getObjectId();
        $this->isActive = $published->isActive();
        $this->sortOrder = $published->getSortOrder();
        $this->createdAt = $published->getCreatedAt();
        $this->updatedAt = $published->getUpdatedAt();
        $this->content = $published->getContent();
        $this->snapshotContent = $published->getSnapshotContent();
    }
}
