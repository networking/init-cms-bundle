<?php

declare(strict_types=1);

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
 * Class UniqueURL.
 *
 * @Annotation
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
class UniqueURL extends Constraint
{
    /**
     * @var string
     */
    public $message = 'error.unique_url';

    /**
     * @return array|string
     */
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
