<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OpenIdFederation\TrustChain\OpenIdFederationTrustChain;
use Sif\Foundation\Security\VerifiableCredentials\Trust\Chain\CredentialTrustChain;

interface OpenIdFederationCredentialTrustChainBridgeInterface
{
    public function map(
        OpenIdFederationTrustChain $chain
    ): CredentialTrustChain;
}
