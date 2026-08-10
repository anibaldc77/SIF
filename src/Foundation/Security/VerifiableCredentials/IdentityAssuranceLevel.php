<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials;

use InvalidArgumentException;

final readonly class IdentityAssuranceLevel
{
    public function __construct(private string $value)
    {
        if (trim($this->value) === '') {
            throw new InvalidArgumentException(
                'Identity assurance level is invalid.'
            );
        }
    }

    public function value(): string
    {
        return $this->value;
    }
}
