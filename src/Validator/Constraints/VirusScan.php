<?php

namespace Networking\InitCmsBundle\Validator\Constraints;

use Symfony\Component\Validator\Constraint;

#[\Attribute(\Attribute::TARGET_PROPERTY)]
class VirusScan extends Constraint
{
    public string $message = "The file is infected with the {{ virus_name }} virus";
}