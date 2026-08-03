<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Validation\Rules;

use Sif\Foundation\Controller\Validation\ValidationIssue;

abstract class AbstractRule
{
    /** @param array<string, scalar|null> $metadata */
    protected function issue(string $code, string $path, string $message, array $metadata = []): ValidationIssue
    {
        return new ValidationIssue($code, $path, $message, $metadata);
    }
}
