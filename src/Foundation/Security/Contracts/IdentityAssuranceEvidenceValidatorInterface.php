<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\IdentityAssuranceEvidence;

interface IdentityAssuranceEvidenceValidatorInterface
{
    public function validate(
        IdentityAssuranceEvidence $evidence
    ): void;
}
