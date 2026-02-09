<?php

namespace Networking\InitCmsBundle\Validator\Constraints;

use Symfony\Component\Validator\Attribute\HasNamedArguments;
use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class VirusScan extends Constraint
{
    public string $message = "The file is infected with the {{ virus_name }} virus";

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
}