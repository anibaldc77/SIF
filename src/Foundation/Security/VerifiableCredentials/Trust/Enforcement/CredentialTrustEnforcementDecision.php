<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Trust\Enforcement;

use Sif\Foundation\Security\VerifiableCredentials\Trust\Decision\CredentialTrustDecision;

final readonly class CredentialTrustEnforcementDecision
{
    public function __construct(
        private CredentialTrustDecision $decision,
        private bool $accepted
    ) {
    }

    public function decision(): CredentialTrustDecision
    {
        return $this->decision;
    }

    public function accepted(): bool
    {
        return $this->accepted;
    }
}
