<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Validation\Rules;

use Sif\Foundation\Controller\Input\RequestInputValue;
use Sif\Foundation\Controller\Validation\ValidationContext;
use Sif\Foundation\Controller\Validation\ValidationRuleInterface;

final class TypeRule extends AbstractRule implements ValidationRuleInterface
{
    public function __construct(private readonly string $type)
    {
        if (!in_array($type, ['string', 'integer', 'float', 'boolean', 'array'], true)) {
            throw new \InvalidArgumentException('Unsupported validation type.');
        }
    }

    public function name(): string { return $this->type; }

    public function validate(RequestInputValue $value, ValidationContext $context, string $path): array
    {
        if (!$value->present() || $value->value() === null) {
            return [];
        }
        $valid = match ($this->type) {
            'string' => is_string($value->value()),
            'integer' => is_int($value->value()),
            'float' => is_float($value->value()) || is_int($value->value()),
            'boolean' => is_bool($value->value()),
            'array' => is_array($value->value()),
            default => throw new \LogicException('Unsupported validation type reached at runtime.'),
        };
        return $valid ? [] : [$this->issue('validation.type', $path, sprintf('The field must be of type %s.', $this->type), ['expected' => $this->type])];
    }
}
