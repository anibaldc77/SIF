<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Formats\SdJwtVc\SdJwtVcCredentialStatus;
use Sif\Foundation\Security\VerifiableCredentials\Formats\SdJwtVc\SdJwtVcCredentialStatusAssessment;

interface SdJwtVcCredentialStatusResolverInterface
{
    public function resolve(
        SdJwtVcCredentialStatus $status
    ): SdJwtVcCredentialStatusAssessment;
}
