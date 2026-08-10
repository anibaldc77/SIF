<?php
declare(strict_types=1);
namespace Sif\Foundation\Security\Contracts;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceProductProfile;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceProductReadinessReport;
interface CredentialIssuanceProductReadinessEvaluatorInterface
{
    public function evaluate(CredentialIssuanceProductProfile $profile): CredentialIssuanceProductReadinessReport;
}
