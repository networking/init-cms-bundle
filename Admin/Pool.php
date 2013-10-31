<?php
/**
 * This file is part of the init_cms_sandbox package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Admin;

use Sonata\AdminBundle\Admin\Pool as AdminPool;

/**
 * Class Pool
 * @package Networking\InitCmsBundle\Admin
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class Pool extends AdminPool
{
    /**
     * Get the groups of admins that will be used to display the
     * admin menu on the side
     *
     * @return array
     */
    public function getDashboardNavigationGroups(){

        $groups = $this->getDashboardGroups();

        $menuGroups = $this->getContainer()->getParameter('networking_init_cms.admin_menu_groups');

        foreach ($menuGroups as $key => $menuGroup) {

            foreach($menuGroup['items'] as $k =>  $item){
                if(array_key_exists($item, $groups)){
                    $menuGroups[$key]['sub_group'][$k] = $groups[$item];
                }

            }
        }

        return $menuGroups;

    }

}
