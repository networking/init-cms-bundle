<?php
/**
 * Created by JetBrains PhpStorm.
 * User: yorkie
 * Date: 30.07.12
 * Time: 09:48
 * To change this template use File | Settings | File Templates.
 */

namespace Networking\InitCmsBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 */
class UniqueURL extends Constraint
{
    public $message = 'The URL "{{ value }}" must be unique, or at least the same as the menu point it is attached to.';

    public function validatedBy()
    {
        // Validator is configured as a service with unique_url_validator as an alias
        return 'networking_init_cms_unique_url_validator';
    }
}
