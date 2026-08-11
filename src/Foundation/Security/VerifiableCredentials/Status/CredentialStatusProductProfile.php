<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Status;

use InvalidArgumentException;

final readonly class CredentialStatusProductProfile
{
    public function __construct(
        private string $name,
        private CredentialStatusProductCapabilities $capabilities
    ) {
        if (trim($this->name) === '') {
            throw new InvalidArgumentException(
                'Credential status product profile name is invalid.'
            );
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    public function capabilities(): CredentialStatusProductCapabilities
    {
        return $this->capabilities;
    }

    public function requireStatusValidation(): bool
    {
        return true;
    }

    public function requireFreshStatusEvidence(): bool
    {
        return true;
    }

    public function requireFailClosedHighAssurance(): bool
    {
        return true;
    }

    public function requireOperationalReadiness(): bool
    {
        return true;
    }
}