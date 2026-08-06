<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Password;

use Sif\Foundation\Security\Exceptions\InvalidPasswordSecretException;

final class PasswordSecret
{
    private const REDACTED = '[REDACTED]';

    private string $value;

    public function __construct(#[\SensitiveParameter] string $value)
    {
        if ($value === '' || strlen($value) > 4096) {
            throw new InvalidPasswordSecretException('Password secret must contain between 1 and 4096 bytes.');
        }

        $this->value = $value;
    }

    public function length(): int
    {
        return strlen($this->value);
    }

    /**
     * @template TResult
     * @param callable(string): TResult $consumer
     * @return TResult
     */
    public function expose(callable $consumer): mixed
    {
        return $consumer($this->value);
    }

    public function __toString(): string
    {
        return self::REDACTED;
    }

    /** @return array<string, string|int> */
    public function __debugInfo(): array
    {
        return [
            'value' => self::REDACTED,
            'length' => $this->length(),
        ];
    }

    /** @return never */
    public function __serialize(): array
    {
        throw new InvalidPasswordSecretException('Password secrets cannot be serialized.');
    }

    public function __clone(): void
    {
        throw new InvalidPasswordSecretException('Password secrets cannot be cloned.');
    }
}
