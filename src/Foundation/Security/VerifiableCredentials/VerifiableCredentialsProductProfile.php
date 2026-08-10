<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials;

use InvalidArgumentException;

final readonly class VerifiableCredentialsProductProfile
{
    public function __construct(
        private string $name,
        private VerifiableCredentialsProductCapabilities $capabilities,
        private bool $requireReplayProtection = true,
        private bool $requireHolderBinding = true,
        private bool $requireCredentialStatus = true,
        private bool $requireIdentityAssurance = true
    ) {
        if (trim($this->name) === '') {
            throw new InvalidArgumentException(
                'Verifiable Credentials product profile name is invalid.'
            );
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    public function capabilities(): VerifiableCredentialsProductCapabilities
    {
        return $this->capabilities;
    }

    public function requireReplayProtection(): bool
    {
        return $this->requireReplayProtection;
    }

    public function requireHolderBinding(): bool
    {
        return $this->requireHolderBinding;
    }

    public function requireCredentialStatus(): bool
    {
        return $this->requireCredentialStatus;
    }

    public function requireIdentityAssurance(): bool
    {
        return $this->requireIdentityAssurance;
    }
}
