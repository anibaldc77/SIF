<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\MultiFactor\Totp;

use Sif\Foundation\Security\Exceptions\InvalidTotpConfigurationException;

final class TotpSecret
{
    private const REDACTED = '[REDACTED]';

    private string $value;

    public function __construct(#[\SensitiveParameter] string $value)
    {
        $normalized = strtoupper(str_replace([' ', '-'], '', trim($value)));

        if (
            strlen($normalized) < 16
            || strlen($normalized) > 256
            || preg_match('/^[A-Z2-7]+$/', $normalized) !== 1
        ) {
            throw new InvalidTotpConfigurationException(
                'TOTP secret must be bounded and encoded with unpadded Base32 characters.'
            );
        }

        $this->value = $normalized;
    }

    public function encodedLength(): int
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
            'encoded_length' => $this->encodedLength(),
        ];
    }

    /** @return never */
    public function __serialize(): array
    {
        throw new InvalidTotpConfigurationException('TOTP secrets cannot be serialized.');
    }

    public function __clone(): void
    {
        throw new InvalidTotpConfigurationException('TOTP secrets cannot be cloned.');
    }
}
