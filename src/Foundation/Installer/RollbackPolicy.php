<?php

declare(strict_types=1);

namespace Sif\Foundation\Installer;

use Sif\Foundation\Installer\Exceptions\InvalidRollbackPolicyException;

final readonly class RollbackPolicy
{
    private const UNSUPPORTED = 'unsupported';
    private const COMPENSATING = 'compensating';
    private const REQUIRED = 'required';

    private string $value;

    public function __construct(string $value)
    {
        $value = strtolower(trim($value));
        if (
            $value === ''
            || strlen($value) > 64
            || preg_match('/^[a-z][a-z0-9-]*$/D', $value) !== 1
        ) {
            throw new InvalidRollbackPolicyException(
                sprintf('Invalid rollback policy "%s".', $value),
            );
        }

        $this->value = $value;
    }

    public static function unsupported(): self
    {
        return new self(self::UNSUPPORTED);
    }

    public static function compensating(): self
    {
        return new self(self::COMPENSATING);
    }

    public static function required(): self
    {
        return new self(self::REQUIRED);
    }

    public function isSupported(): bool
    {
        return $this->value !== self::UNSUPPORTED;
    }

    public function isRequired(): bool
    {
        return $this->value === self::REQUIRED;
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return $this->value === $other->value;
    }

    public function __toString(): string
    {
        return $this->value;
    }
}
