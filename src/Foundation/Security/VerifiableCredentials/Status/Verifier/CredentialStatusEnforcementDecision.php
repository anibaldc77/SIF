<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Status\Verifier;

use Sif\Foundation\Security\VerifiableCredentials\Status\CredentialStatusAssessment;

final readonly class CredentialStatusEnforcementDecision
{
    public function __construct(
        private CredentialStatusAssessment $assessment
    ) {
    }

    public function assessment(): CredentialStatusAssessment
    {
        return $this->assessment;
    }

    public function accepted(): bool
    {
        return $this->assessment->valid();
    }
}