<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */
declare(strict_types=1);

namespace Networking\InitCmsBundle\Entity;

use Networking\InitCmsBundle\Model\Text as ModelText;

/**
 * Class BaseText.
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
abstract class BaseText extends ModelText
{
    public function prePersist(): void
    {
        $this->createdAt = $this->updatedAt = new \DateTime();
    }

    public function preUpdate(): void
    {
        $this->updatedAt = new \DateTime();
    }
}
