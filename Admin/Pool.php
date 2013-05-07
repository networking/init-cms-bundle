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
use Sonata\AdminBundle\Admin\Admin;

/**
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class Pool extends AdminPool
{

    public function getDashboardNavigationGroups(){

        $groups = $this->getDashboardGroups();

        $menuGroups = $this->getContainer()->getParameter('networking_init_cms.admin_menu_groups');

        foreach ($menuGroups as $key => $menuGroup) {

            foreach($menuGroup['items'] as $k =>  $item){
                $menuGroups[$key]['sub_group'][$k] = $groups[$item];
            }

        }

        return $menuGroups;

    }

}
