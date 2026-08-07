<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\MultiFactor\Totp;

use Sif\Foundation\Security\Exceptions\InvalidTotpConfigurationException;

final readonly class TotpHashAlgorithm
{
    private const ALLOWED = ['sha1', 'sha256', 'sha512'];

    private string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));

        if (!in_array($normalized, self::ALLOWED, true)) {
            throw new InvalidTotpConfigurationException('TOTP hash algorithm must be SHA-1, SHA-256 or SHA-512.');
        }

        $this->value = $normalized;
    }

    public static function sha1(): self
    {
        return new self('sha1');
    }

    public static function sha256(): self
    {
        return new self('sha256');
    }

    public static function sha512(): self
    {
        return new self('sha512');
    }

    public function value(): string
    {
        return $this->value;
    }

    public function equals(self $other): bool
    {
        return hash_equals($this->value, $other->value);
    }
}
