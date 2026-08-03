<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Exceptions;

use RuntimeException;
use Sif\Foundation\Controller\Validation\ValidationResult;

final class ControllerValidationException extends RuntimeException
{
    public function __construct(private readonly ValidationResult $result)
    {
        parent::__construct('Controller input validation failed.');
    }

    public function result(): ValidationResult
    {
        return $this->result;
    }
}
