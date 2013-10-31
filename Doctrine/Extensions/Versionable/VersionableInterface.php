<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Doctrine\Extensions\Versionable;

/**
 * Class VersionableInterface
 * @package Networking\InitCmsBundle\Doctrine\Extensions\Versionable
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
interface VersionableInterface
{
    /**
     * @var string
     */
    const STATUS_DRAFT = 'status_draft';

    /**
     * @var string
     */
    const STATUS_REVIEW = 'status_review';

    /**
     * @var string
     */
    const STATUS_PUBLISHED = 'status_published';

    /**
     * @return mixed
     */
    public function getSnapshot();

    /**
     * @return mixed
     */
    public function getSnapshotClassType();

    /**
     * @return int
     */
    public function getCurrentVersion();

    /**
     * @return int
     */
    public function getResourceId();

    /**
     * @return array
     */
    public function hasListener();

}
