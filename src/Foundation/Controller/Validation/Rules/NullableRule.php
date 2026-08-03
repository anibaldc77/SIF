<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Validation\Rules;

use Sif\Foundation\Controller\Input\RequestInputValue;
use Sif\Foundation\Controller\Validation\ValidationContext;
use Sif\Foundation\Controller\Validation\ValidationRuleInterface;

final class NullableRule extends AbstractRule implements ValidationRuleInterface
{
    public function name(): string { return 'nullable'; }
    public function validate(RequestInputValue $value, ValidationContext $context, string $path): array { return []; }
}
