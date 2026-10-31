<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance;

final readonly class OpenId4VcHaipCredentialProofRequirements
{
    public function __construct(
        private bool $keyAttestationRequired
    ) {
    }

    public function keyAttestationRequired(): bool
    {
        return $this->keyAttestationRequired;
    }
}