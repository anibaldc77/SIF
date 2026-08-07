<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\MultiFactor\Totp;

use Sif\Foundation\Security\Exceptions\InvalidTotpConfigurationException;

final class TotpCode
{
    private const REDACTED = '[REDACTED]';

    private string $value;

    public function __construct(#[\SensitiveParameter] string $value)
    {
        $normalized = trim($value);

        if (preg_match('/^[0-9]{6,8}$/', $normalized) !== 1) {
            throw new InvalidTotpConfigurationException('TOTP code must contain between 6 and 8 decimal digits.');
        }

        $this->value = $normalized;
    }

    public function digits(): int
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
            'digits' => $this->digits(),
        ];
    }

    /** @return never */
    public function __serialize(): array
    {
        throw new InvalidTotpConfigurationException('TOTP codes cannot be serialized.');
    }

    public function __clone(): void
    {
        throw new InvalidTotpConfigurationException('TOTP codes cannot be cloned.');
    }
}
