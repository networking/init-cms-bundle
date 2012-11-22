<?php

namespace Networking\InitCmsBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 *
 * @api
 */
class Path extends Constraint
{
    public $message = 'This value is not a valid Path.';
}
