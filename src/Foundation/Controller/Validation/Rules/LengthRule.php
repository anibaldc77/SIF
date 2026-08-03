<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Validation\Rules;

use Sif\Foundation\Controller\Input\RequestInputValue;
use Sif\Foundation\Controller\Validation\ValidationContext;
use Sif\Foundation\Controller\Validation\ValidationRuleInterface;

final class LengthRule extends AbstractRule implements ValidationRuleInterface
{
    public function __construct(private readonly int $length)
    {
        if ($length < 0) throw new \InvalidArgumentException('Length cannot be negative.');
    }
    public function name(): string { return 'length'; }
    public function validate(RequestInputValue $value, ValidationContext $context, string $path): array
    {
        if (!$value->present() || $value->value() === null) return [];
        $actual = is_string($value->value()) ? strlen($value->value()) : (is_array($value->value()) ? count($value->value()) : null);
        if ($actual === null || $actual === $this->length) return [];
        return [$this->issue('validation.length', $path, 'The field has an invalid length.', ['length' => $this->length])];
    }
}
