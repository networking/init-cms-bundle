<?php
/*
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Doctrine\Extensions\Versionable;

/**
 *
 */
interface VersionableInterface
{
    const STATUS_DRAFT = 'draft';

    const STATUS_REVIEW = 'review';

    const STATUS_PUBLISHED = 'published';

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
