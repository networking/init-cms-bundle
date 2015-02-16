<?php
/**
 * This file is part of the billag  package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Serializer;

use JMS\Serializer\DeserializationContext;

class PageSnapshotDeserializationContext extends DeserializationContext{

    /**
     * @var bool
     */
    protected $deserializeTranslations = true;

    /**
     * @return bool
     */
    public function deserializeTranslations()
    {
        return $this->deserializeTranslations;
    }

    /**
     * @param bool $deserialize
     */
    public function setDeserializeTranslations($deserialize = true)
    {
        $this->deserializeTranslations =  $deserialize;
    }
} 