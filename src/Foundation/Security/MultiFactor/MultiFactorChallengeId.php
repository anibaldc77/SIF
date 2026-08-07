<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\MultiFactor;

use Sif\Foundation\Security\Exceptions\InvalidMultiFactorChallengeException;

final readonly class MultiFactorChallengeId
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
            throw new InvalidMultiFactorChallengeException(
                'Multi-factor challenge identifier must be stable, bounded and transport-safe.'
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
