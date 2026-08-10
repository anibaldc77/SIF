<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialConfiguration;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuerMetadata;

interface CredentialConfigurationResolverInterface
{
    public function resolve(
        CredentialIssuerMetadata $metadata,
        string $configurationId
    ): CredentialConfiguration;
}
