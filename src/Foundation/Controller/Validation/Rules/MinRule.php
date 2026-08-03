<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Validation\Rules;

use Sif\Foundation\Controller\Input\RequestInputValue;
use Sif\Foundation\Controller\Validation\ValidationContext;
use Sif\Foundation\Controller\Validation\ValidationRuleInterface;

final class MinRule extends AbstractRule implements ValidationRuleInterface
{
    public function __construct(private readonly int|float $minimum) {}
    public function name(): string { return 'min'; }
    public function validate(RequestInputValue $value, ValidationContext $context, string $path): array
    {
        if (!$value->present() || $value->value() === null) return [];
        $actual = self::measure($value->value());
        if ($actual === null || $actual >= $this->minimum) return [];
        return [$this->issue('validation.min', $path, 'The field is below the minimum.', ['minimum' => $this->minimum])];
    }
    private static function measure(mixed $value): int|float|null
    {
        if (is_int($value) || is_float($value)) return $value;
        if (is_string($value)) return strlen($value);
        if (is_array($value)) return count($value);
        return null;
    }
}
