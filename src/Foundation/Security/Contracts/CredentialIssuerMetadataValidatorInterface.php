<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuerMetadata;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuerMetadataAssessment;

interface CredentialIssuerMetadataValidatorInterface
{
    public function validate(
        CredentialIssuerMetadata $metadata
    ): CredentialIssuerMetadataAssessment;
}
