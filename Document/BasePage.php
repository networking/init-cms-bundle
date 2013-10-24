<?php

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Document;


use Networking\InitCmsBundle\Model\Page as ModelPage;
/**
 * Networking\InitCmsBundle\Document\BasePage
 *
 * @author net working AG <info@networking.ch>
 */
abstract class BasePage extends ModelPage
{
    public function getContentRoute(){
        if(!$this->contentRoute){
            $this->contentRoute = new ContentRoute();
        }

        return $this->contentRoute;
    }
}
