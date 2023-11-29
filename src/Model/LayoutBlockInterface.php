<?php

declare(strict_types=1);

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
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
interface LayoutBlockInterface extends ContentInterface
{
    /**
     * @return string
     */
    public function __toString();

    /**
     * Set zone.
     *
     * @param string $zone
     *
     * @return $this
     */
    public function setZone($zone);

    /**
     * Get zone.
     *
     * @return string
     */
    public function getZone();

    /**
     * Set page.
     *
     * @param PageInterface $page
     *
     * @return $this
     */
    public function setPage(PageInterface $page);

    /**
     * Get conversation.
     *
     * @return Page
     */
    public function getPage();

    /**
     * Get conversation.
     *
     * @return Page
     */
    public function getPageId();

    /**
     * Set isActive.
     *
     * @param bool $active
     *
     * @return $this
     */
    public function setIsActive($active);

    /**
     * Get isActive.
     *
     * @return bool
     */
    public function getIsActive();

    /**
     * Alias for getIsActive.
     *
     * @return bool
     */
    public function isActive();

    /**
     * Set createdAt.
     *
     * @return $this
     */
    public function setCreatedAt();

    /**
     * Get createdAt.
     *
     * @return \DateTime
     */
    public function getCreatedAt();

    /**
     * Set updatedAt.
     *
     * @param \DateTime $updatedAt
     *
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * Get updatedAt.
     *
     * @return \DateTime
     */
    public function getUpdatedAt();

    /**
     * @param int $sortOrder
     *
     * @return $this
     */
    public function setSortOrder($sortOrder);

    /**
     * @return int
     */
    public function getSortOrder();


    /**
     * @return array|bool
     */
    public static function getFieldDefinition();

    /**
     * @param LayoutBlockInterface $object
     *
     * @return mixed
     */
    public function import(LayoutBlockInterface $object);

    /**
     * @param LayoutBlockInterface $published
     * @return mixed
     */
    public function restoreFormPublished(LayoutBlockInterface $published);

    /**
     * @param array $published
     * @return mixed
     */
    public function restoreFormSerializer(array $published);
}
