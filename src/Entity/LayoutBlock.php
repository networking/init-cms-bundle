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

use Doctrine\ORM\Mapping as ORM;
use Networking\InitCmsBundle\Model\LayoutBlockInterface;
use Networking\InitCmsBundle\Model\PageInterface;
use Symfony\Component\Serializer\Attribute\Ignore;

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
    protected ?int $id = null;

    /**
     * @var string
     */
    #[ORM\Column(type: 'string', length: 255)]
    protected $zone;

    /**
     * @var PageInterface
     */
    #[Ignore]
    protected $page;

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

    protected string $classType = '';

    public function __clone(): void
    {
        $this->id = null;
        $this->page = null;
    }

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
        if (!$this->page) {
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
     * @param array $params
     */
    #[Ignore]
    public function getTemplateOptions($params = []): array
    {
        return [];
    }

    /**
     * @return array|bool
     */
    #[Ignore]
    public static function getFieldDefinition()
    {
        return false;
    }

    /**
     * @return array|bool
     */
    #[Ignore]
    public function getAdminContent(): array
    {
        return [];
    }

    /**
     * @return string
     */
    #[Ignore]
    public function getContentTypeName()
    {
        return null;
    }

    public function import(LayoutBlockInterface $object)
    {
        foreach (get_object_vars($object) as $key => $value) {
            if ('id' != $key) {
                $this->$key = $value;
            }
        }
    }

    public function restoreFormPublished(
        LayoutBlockInterface $published)
    {
        $this->zone = $published->getZone();
        $this->isActive = $published->isActive();
        $this->sortOrder = $published->getSortOrder();
        $this->createdAt = $published->getCreatedAt();
        $this->updatedAt = $published->getUpdatedAt();
    }

    public function restoreFormSerializer(
        array $published)
    {
        $this->zone = $published['zone'];
        $this->isActive = $published['isActive'];
        $this->sortOrder = $published['sortOrder'];
        $this->createdAt = $published['createdAt'];
        $this->updatedAt = $published['updatedAt'];
    }

    public function getTemplate(): ?string
    {
        $adminContent = $this->getAdminContent();

        return array_key_exists('template', $adminContent) ? $adminContent['template'] : null;
    }
}
