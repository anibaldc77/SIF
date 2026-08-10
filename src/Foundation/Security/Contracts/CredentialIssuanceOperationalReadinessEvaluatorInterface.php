<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceOperationalContext;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceOperationalReadinessReport;

interface CredentialIssuanceOperationalReadinessEvaluatorInterface
{
    public function evaluate(
        CredentialIssuanceOperationalContext $context
    ): CredentialIssuanceOperationalReadinessReport;
}
