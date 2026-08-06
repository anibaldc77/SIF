<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Authentication;

use Sif\Foundation\Security\Exceptions\InvalidAuthenticationEvidenceException;

final readonly class AuthenticationMethod
{
    public function __construct(private string $value)
    {
        if (preg_match('/^[a-z][a-z0-9_.:-]{0,127}$/D', $value) !== 1) {
            throw new InvalidAuthenticationEvidenceException(
                'Authentication method must use a stable lowercase identifier of at most 128 characters.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
