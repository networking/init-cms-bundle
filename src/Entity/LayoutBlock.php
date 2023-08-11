<?php

declare(strict_types=1);

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Networking\InitCmsBundle\Model\ContentInterface;
use Networking\InitCmsBundle\Model\LayoutBlockInterface;
use Networking\InitCmsBundle\Model\PageInterface;

/**
 * Networking\InitCmsBundle\Entity\LayoutBlock.
 *
 * @author net working AG <info@networking.ch>
 */
#[ORM\InheritanceType('JOINED')]
#[ORM\DiscriminatorColumn(name: 'class_type', type: 'string')]
#[ORM\Entity]
#[ORM\Table(name: 'layout_block')]
#[ORM\HasLifecycleCallbacks]
abstract class LayoutBlock implements LayoutBlockInterface, \Stringable
{
    /**
     * @var int
     */
    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue(strategy: 'AUTO')]
    protected $id;


    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255)]
    protected $zone;

    /**
     * @var PageInterface
     */

    protected $page = null;


    /**
     * @var bool
     */
    #[ORM\Column(type: 'boolean')]
    protected $isActive = true;

    /**
     * @var int
     */
    #[ORM\Column(type: 'integer', nullable: true)]
    protected $sortOrder;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime')]
    protected $createdAt;

    /**
     * @var \DateTime
     */
    #[ORM\Column(type: 'datetime')]
    protected $updatedAt;

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

    protected string $classType;

    public function __construct()
    {
        $this->snapshotContent = new ArrayCollection();
    }

    public function __clone()
    {
        $this->id = null;
        $this->page = null;
    }

    /**
     * @return string
     */
    public function __toString(): string
    {
        return $this->getContentTypeName();
    }

    #[ORM\PrePersist]
    public function prePersist()
    {
        $this->createdAt = $this->updatedAt = new \DateTime('now');
    }

    #[ORM\PreUpdate]
    public function preUpdate()
    {
        $this->updatedAt = new \DateTime('now');
    }

    public function getClassType(): string
    {
        return $this::class;
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
     * @return bool
     */
    public function getIsSnapshot()
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

    public function restoreFormPublished(
        LayoutBlockInterface $published){
        $this->zone = $published->getZone();
        $this->isActive = $published->isActive();
        $this->sortOrder = $published->getSortOrder();
        $this->createdAt = $published->getCreatedAt();
        $this->updatedAt = $published->getUpdatedAt();
        $this->snapshotContent = $published->getSnapshotContent();
    }
}
