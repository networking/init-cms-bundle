<?php
/**
 * This file is part of the forel-2016  package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Model;


interface SearchableContentInterface
{
    /**
     * @return string
     */
    public function getSearchableContent();
}