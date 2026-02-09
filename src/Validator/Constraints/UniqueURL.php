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

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

/**
 * Class UniqueURL.
 *
 * @Annotation
 *
 * @author Yorkie Chadwick <y.chadwick@networking.ch>
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class UniqueURL extends Constraint
{
    /**
     * @var string
     */
    public $message = 'error.unique_url';

    #[HasNamedArguments]
    public function __construct(
      ?array $options = null,
      ?string $message = null,
      ?array $groups = null,
      mixed $payload = null,
    ) {
        if (\is_array($options)) {
            trigger_deprecation('symfony/validator', '7.3', 'Passing an array of options to configure the "%s" constraint is deprecated, use named arguments instead.', static::class);
        }

        parent::__construct($options, $groups, $payload);

        $this->message = $message ?? $this->message;
    }

    /**
     * @return array|string
     */
    public function getTargets(): string
    {
        return self::CLASS_CONSTRAINT;
    }
}
