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
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
interface LayoutBlockInterface extends ContentInterface {
    /**
         * @return string
         */
        public function __toString();

        /**
         * Set name
         *
         * @param  string $name
         * @return $this
         */
        public function setName($name);

        /**
         * Get name
         *
         * @return string
         */
        public function getName();

        /**
         * Set zone
         *
         * @param  string $zone
         * @return $this
         */
        public function setZone($zone);

        /**
         * Get zone
         *
         * @return string
         */
        public function getZone();

        /**
         * Set page
         *
         * @param  PageInterface $page
         * @return $this
         */
        public function setPage(PageInterface $page);

        /**
         * Get conversation
         *
         * @return Page
         */
        public function getPage();

        /**
         * Get conversation
         *
         * @return Page
         */
        public function getPageId();

        /**
         * @param  string $classType
         * @return $this
         */
        public function setClassType($classType);

        /**
         * @return string
         */
        public function getClassType();

        /**
         * @return string
         */
        public function getOrigClassType();

        /**
         * @param  int $objectId
         * @return $this
         */
        public function setObjectId($objectId);

        /**
         * @return int
         */
        public function getObjectId();

        /**
         * Set isActive
         *
         * @param  boolean $active
         * @return $this
         */
        public function setIsActive($active);

        /**
         * Get isActive
         *
         * @return boolean
         */
        public function getIsActive();

        /**
         * Alias for getIsActive
         *
         * @return bool
         */
        public function isActive();

        /**
         * Set createdAt
         *
         * @return $this
         */
        public function setCreatedAt();

        /**
         * Get createdAt
         *
         * @return \DateTime
         */
        public function getCreatedAt();

        /**
         * Set updatedAt
         *
         * @param  \DateTime $updatedAt
         * @return $this
         */
        public function setUpdatedAt($updatedAt);

        /**
         * Get updatedAt
         *
         * @return \DateTime
         */
        public function getUpdatedAt();

        /**
         * @param  int $sortOrder
         * @return $this
         */
        public function setSortOrder($sortOrder);

        /**
         * @return int
         */
        public function getSortOrder();
        /**
         * @return array
         */
        public function getContent();
        /**
         * @param $content
         * @param $key
         * @return $this
         */
        public function setContent($content, $key = null);

        /**
         * @param boolean $isSnapshot
         * @return \Networking\InitCmsBundle\Model\LayoutBlockInterface
         */
        public function setIsSnapshot($isSnapshot);

        /**
         * @param boolean $isSnapshot
         * @return \Networking\InitCmsBundle\Model\LayoutBlockInterface
         */
        public function setNoAutoDraft($isSnapshot);

        /**
         * @return boolean
         */
        public function getIsSnapshot();

        /**
         * @return boolean
         */
        public function getSetNoAutoDraft();

        /**
         * @param array $snapshotContent
         * @return \Networking\InitCmsBundle\Model\LayoutBlockInterface
         */
        public function setSnapshotContent($snapshotContent);

        /**
         * @return array
         */
        public function getSnapshotContent();

        /**
         * @param $snapshotContent
         * @return void
         * @internal param $content
         */
        public function takeSnapshot($snapshotContent);

        /**
         * @return array|bool
         */
        public static function getFieldDefinition();

        /**
         * @return array|bool
         */
        public function getAdminContent();

        /**
         * @return string
         */
        public function getContentTypeName();

        public function import(LayoutBlockInterface $object);
}