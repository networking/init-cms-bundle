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
 * Class PageSnapshotManagerInterface.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
interface PageSnapshotManagerInterface
{
    /**
     * @param $pageId
     *
     * @return mixed
     */
    public function findSnapshotByPageId($pageId);

    /**
     * @param $pageId
     *
     * @return mixed
     */
    public function findLastPageSnapshot($pageId);

    /**
     * @return string
     */
    public function getClassName();
}
