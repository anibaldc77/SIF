<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Context;

use InvalidArgumentException;

/**
 * Immutable, explicitly named group of structured logging attributes.
 */
final readonly class ScopedLogAttributes
{
    /** @var array<string, null|bool|int|float|string|array<mixed>> */
    private array $attributes;

    /**
     * @param array<string, null|bool|int|float|string|array<mixed>> $attributes
     */
    public function __construct(private string $scope, array $attributes = [])
    {
        if (!preg_match('/^[a-z][a-z0-9_.-]*$/', $scope)) {
            throw new InvalidArgumentException('Log attribute scope must be a portable lowercase identifier.');
        }

        foreach ($attributes as $key => $value) {
            if (!is_string($key) || trim($key) === '') {
                throw new InvalidArgumentException('Scoped log attribute keys must be non-empty strings.');
            }

            if (!$this->isSupportedValue($value)) {
                throw new InvalidArgumentException(sprintf('Scoped log attribute "%s" contains an unsupported value.', $key));
            }
        }

        $this->attributes = $attributes;
    }

    public function scope(): string
    {
        return $this->scope;
    }

    /** @return array<string, null|bool|int|float|string|array<mixed>> */
    public function attributes(): array
    {
        return $this->attributes;
    }

    /** @return array<string, array<string, null|bool|int|float|string|array<mixed>>> */
    public function nested(): array
    {
        return [$this->scope => $this->attributes];
    }

    /** @return array<string, null|bool|int|float|string|array<mixed>> */
    public function prefixed(): array
    {
        $prefixed = [];
        foreach ($this->attributes as $key => $value) {
            $prefixed[$this->scope . '.' . $key] = $value;
        }

        return $prefixed;
    }

    private function isSupportedValue(mixed $value): bool
    {
        if ($value === null || is_scalar($value)) {
            return true;
        }
        if (!is_array($value)) {
            return false;
        }
        foreach ($value as $nested) {
            if (!$this->isSupportedValue($nested)) {
                return false;
            }
        }

        return true;
    }
}
