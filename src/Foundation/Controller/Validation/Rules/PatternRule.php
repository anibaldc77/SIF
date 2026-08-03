<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Validation\Rules;

use Sif\Foundation\Controller\Input\RequestInputValue;
use Sif\Foundation\Controller\Validation\ValidationContext;
use Sif\Foundation\Controller\Validation\ValidationRuleInterface;

final class PatternRule extends AbstractRule implements ValidationRuleInterface
{
    public function __construct(private readonly string $pattern)
    {
        if (@preg_match($pattern, '') === false) throw new \InvalidArgumentException('Invalid validation pattern.');
    }
    public function name(): string { return 'pattern'; }
    public function validate(RequestInputValue $value, ValidationContext $context, string $path): array
    {
        if (!$value->present() || $value->value() === null || !is_string($value->value())) return [];
        return preg_match($this->pattern, $value->value()) === 1 ? [] : [$this->issue('validation.pattern', $path, 'The field format is invalid.')];
    }
}
