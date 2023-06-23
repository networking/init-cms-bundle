<?php

declare(strict_types=1);

/**
 * Created by PhpStorm.
 * User: marcbissegger
 * Date: 10/24/13
 * Time: 3:24 PM.
 */
namespace Networking\InitCmsBundle\Model;

/**
 * Class MenuItemManagerInterface.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
interface MenuItemManagerInterface
{
    /**
     * @param $locale
     * @param null   $sortByField
     * @param string $direction
     *
     * @return array
     */
    public function getRootNodesByLocale($locale, $sortByField = null, $direction = 'asc');

    /**
     * @param null   $node
     * @param bool   $direct
     * @param null   $sortByField
     * @param string $direction
     * @param bool   $includeNode
     * @param $viewStatus
     *
     * @return mixed
     */
    public function getChildrenByStatus(
        $node = null,
        $direct = false,
        $sortByField = null,
        $direction = 'ASC',
        $includeNode = false,
        $viewStatus = Page::STATUS_PUBLISHED
    );

    /**
     * Finds a single object by a set of criteria.
     *
     * @param array $criteria The criteria
     *
     * @return object The object
     */
    public function findOneBy(array $criteria);
}
