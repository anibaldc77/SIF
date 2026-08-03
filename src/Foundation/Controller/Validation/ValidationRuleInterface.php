<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Validation;

use Sif\Foundation\Controller\Input\RequestInputValue;

interface ValidationRuleInterface
{
    public function name(): string;

    /** @return list<ValidationIssue> */
    public function validate(
        RequestInputValue $value,
        ValidationContext $context,
        string $path,
    ): array;
}
