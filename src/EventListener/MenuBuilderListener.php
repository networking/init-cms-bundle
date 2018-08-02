<?php
/**
 * Created by PhpStorm.
 * User: yorkie
 * Date: 02.08.18
 * Time: 13:16
 */

namespace Networking\InitCmsBundle\EventListener;

use Sonata\AdminBundle\Event\ConfigureMenuEvent;

class MenuBuilderListener {
	public function addMenuItems(ConfigureMenuEvent $event)
	{
		$menu = $event->getMenu();

	}
}