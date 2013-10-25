<?php
/**
 * Created by PhpStorm.
 * User: marcbissegger
 * Date: 10/24/13
 * Time: 4:37 PM
 */

namespace Networking\InitCmsBundle\Model;


interface HelpTextManagerInterface {


    /**
     * @param $translationKey
     * @param $locale
     * @return object
     */
    public function getHelpTextByKeyLocale($translationKey, $locale );


    /**
     * @param $translationKey
     * @param $locale
     * @return array
     */
    public function searchHelpTextByKeyLocale($translationKey, $locale );


} 