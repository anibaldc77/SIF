<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authentication;

use Sif\Foundation\Security\Exceptions\InvalidAuthenticationEvidenceException;

final readonly class AuthenticationLevel
{
    public function __construct(private int $value)
    {
        if ($value < 0 || $value > 100) {
            throw new InvalidAuthenticationEvidenceException('Authentication level must be between 0 and 100.');
        }
    }

    public function value(): int
    {
        return $this->value;
    }

    public function satisfies(self $required): bool
    {
        return $this->value >= $required->value;
    }
}
