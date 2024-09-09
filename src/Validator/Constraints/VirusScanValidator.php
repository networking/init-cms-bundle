<?php

namespace Networking\InitCmsBundle\Validator\Constraints;

use Sineflow\ClamAV\Scanner;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

class VirusScanValidator extends ConstraintValidator
{

    protected ?Scanner $scanner = null;

    public function setScanner(Scanner $scanner): void
    {
        $this->scanner = $scanner;
    }

    /**
     * @inheritDoc
     */
    public function validate(mixed $value, Constraint $constraint)
    {
        if(!$this->scanner instanceof Scanner) {
            @trigger_error(
                'This validator can only be used with the sineflow/clamav scanner library and the unix clamAV command.',
                \E_USER_WARNING
            );
            return;
        }

        if ($value !== null && $value !== '' && $value instanceof UploadedFile ) {
                $result = $this->scanner->scan($value);
                if (!$result->isClean()) {
                    $this->context->buildViolation($constraint->message)
                        ->setParameter('{{ virus_name }}', $result->getVirusName())
                        ->addViolation();
                }
            }

    }
}