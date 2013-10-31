<?php
/**
 * This file is part of the Networking package.
 *
 * (c) net working AG <info@networking.ch>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Networking\InitCmsBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * Class UniqueURL
 *
 * @Annotation
 *
 * @package Networking\InitCmsBundle\Validator\Constraints
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class UniqueURL extends Constraint
{
    /**
     * @var string $message
     */
    public $message = 'error.unique_url';

    /**
     * @return string
     */
    public function validatedBy()
    {
        // Validator is configured as a service with unique_url_validator as an alias
        return 'unique_url_validator';
    }

    /**
     * @return array|string
     */
    public function getTargets()
    {
        return self::CLASS_CONSTRAINT;
    }
}
