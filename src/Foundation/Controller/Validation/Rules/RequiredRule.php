<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Validation\Rules;

use Sif\Foundation\Controller\Input\RequestInputValue;
use Sif\Foundation\Controller\Validation\ValidationContext;
use Sif\Foundation\Controller\Validation\ValidationRuleInterface;

final class RequiredRule extends AbstractRule implements ValidationRuleInterface
{
    public function name(): string { return 'required'; }

    public function validate(RequestInputValue $value, ValidationContext $context, string $path): array
    {
        $invalid = !$value->present() || $value->value() === null || $value->value() === '';
        return $invalid ? [$this->issue('validation.required', $path, 'The field is required.')] : [];
    }
}
