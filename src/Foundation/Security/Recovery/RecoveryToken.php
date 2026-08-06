<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Recovery;

use Sif\Foundation\Security\Exceptions\InvalidRecoveryTokenException;

final class RecoveryToken
{
    private const REDACTED = '[REDACTED]';

    private string $value;

    public function __construct(#[\SensitiveParameter] string $value)
    {
        if (
            strlen($value) < 32
            || strlen($value) > 512
            || preg_match('/^[A-Za-z0-9_-]+$/', $value) !== 1
        ) {
            throw new InvalidRecoveryTokenException(
                'Recovery token must be bounded and encoded with URL-safe characters.'
            );
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
        throw new InvalidRecoveryTokenException('Recovery tokens cannot be serialized.');
    }

    public function __clone(): void
    {
        throw new InvalidRecoveryTokenException('Recovery tokens cannot be cloned.');
    }
}
