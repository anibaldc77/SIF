<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Trust\Product;

use InvalidArgumentException;

final readonly class CredentialTrustProductProfile
{
    public function __construct(
        private string $name,
        private CredentialTrustProductCapabilities $capabilities
    ) {
        if (trim($this->name) === '') {
            throw new InvalidArgumentException(
                'Credential trust product profile name is invalid.'
            );
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    public function capabilities(): CredentialTrustProductCapabilities
    {
        return $this->capabilities;
    }

    public function requireValidatedTrustChain(): bool
    {
        return true;
    }

    public function requireCurrentTrustEvidence(): bool
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
