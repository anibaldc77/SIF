<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Formats\SdJwtVc\SdJwtVcIssuerIdentity;
use Sif\Foundation\Security\VerifiableCredentials\Formats\SdJwtVc\SdJwtVcIssuerTrustAssessment;

interface SdJwtVcIssuerTrustEvaluatorInterface
{
    public function evaluate(
        SdJwtVcIssuerIdentity $issuer
    ): SdJwtVcIssuerTrustAssessment;
}
