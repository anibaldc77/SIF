<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationEntityStatement;
use Sif\Foundation\Security\VerifiableCredentials\Trust\CredentialTrustEntityReference;

interface OpenIdFederationTrustBridgeInterface
{
    public function mapEntity(
        OpenIdFederationEntityStatement $statement
    ): CredentialTrustEntityReference;
}
