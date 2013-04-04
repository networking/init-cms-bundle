<?php

/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
namespace Networking\InitCmsBundle\Entity;

use Networking\InitCmsBundle\Entity\BaseText,
    Doctrine\ORM\Mapping as ORM,
    Ibrows\Bundle\SonataAdminAnnotationBundle\Annotation as Sonata;


/**
 * Networking\InitCmsBundle\Entity\Content
 *
 * @ORM\Table(name="text")
 * @ORM\Entity(repositoryClass="Networking\InitCmsBundle\Entity\TextRepository")
 *
 * @author net working AG <info@networking.ch>
 */
class Text extends BaseText implements ContentInterface
{
}
