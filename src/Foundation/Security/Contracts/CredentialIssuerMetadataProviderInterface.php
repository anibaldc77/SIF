<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuerMetadata;

interface CredentialIssuerMetadataProviderInterface
{
    public function metadata(): CredentialIssuerMetadata;
}
