<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\MultiFactor\Totp;

use Sif\Foundation\Security\Exceptions\InvalidTotpFactorException;

final readonly class TotpFactorId
{
    private string $value;

    public function __construct(string $value)
    {
        $normalized = strtolower(trim($value));

        if (
            $normalized === ''
            || strlen($normalized) > 128
            || preg_match('/^[a-z0-9][a-z0-9._:-]*$/', $normalized) !== 1
        ) {
            throw new InvalidTotpFactorException(
                'TOTP factor identifier must be stable, bounded and transport-safe.'
            );
        }

        $this->value = $normalized;
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
