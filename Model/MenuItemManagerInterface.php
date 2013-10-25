<?php
/**
 * Created by PhpStorm.
 * User: marcbissegger
 * Date: 10/24/13
 * Time: 3:24 PM
 */

namespace Networking\InitCmsBundle\Model;


interface MenuItemManagerInterface {

    /**
    * @param $locale
    * @param  null $sortByField
    * @param  string $direction
    * @return array
    */
    public function getRootNodesByLocale($locale, $sortByField = null, $direction = 'asc');


    public function getChildrenByStatus($node = null, $direct = false, $sortByField = null, $direction = 'ASC', $includeNode = false, $viewStatus = BasePage::STATUS_PUBLISHED);

} 