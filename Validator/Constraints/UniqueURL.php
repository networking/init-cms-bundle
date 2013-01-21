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
 * @author net working AG <info@networking.ch>
 * @Annotation
 */
class UniqueURL extends Constraint
{
    /**
     * @var string $message
     */
    public $message = 'The URL "{{ value }}" must be unique, or at least the same as the menu point it is attached to.';

    /**
     * @return string
     */
    public function validatedBy()
    {
        // Validator is configured as a service with unique_url_validator as an alias
        return 'networking_init_cms_unique_url_validator';
    }
}
