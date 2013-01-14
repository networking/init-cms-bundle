<?php

/*
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
 * @Annotation
 *
 * @author net working AG <info@networking.ch>
 * @author Bernhard Schussek <bschussek@gmail.com>
 */
class Path extends Constraint
{
    public $message = 'This value is not a valid Path.';
}
