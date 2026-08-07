<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\MultiFactor;

use Sif\Foundation\Security\Exceptions\InvalidMultiFactorChallengeException;

final readonly class MultiFactorType
{
    private string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));

        if (
            $normalized === ''
            || strlen($normalized) > 64
            || preg_match('/^[a-z][a-z0-9._-]*$/', $normalized) !== 1
        ) {
            throw new InvalidMultiFactorChallengeException(
                'Multi-factor type must be stable, bounded and provider-neutral.'
            );
        }

        $this->value = $normalized;
    }

    public static function totp(): self
    {
        return new self('totp');
    }

    public static function recoveryCode(): self
    {
        return new self('recovery_code');
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
