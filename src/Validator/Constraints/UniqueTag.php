<?php
/**
 * This file is part of the demo_cms  package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

class UniqueTag extends Constraint
{
    /**
     * @var string
     */
    public $message = 'error.unique_tag';

    /**
     * @return array|string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
