<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationStatementValidationContext;
use Sif\Foundation\Security\OpenIdFederation\TrustChain\OpenIdFederationTrustChain;
use Sif\Foundation\Security\OpenIdFederation\TrustChain\OpenIdFederationTrustChainValidationResult;

interface OpenIdFederationTrustChainValidationPolicyInterface
{
    public function validate(
        OpenIdFederationTrustChain $chain,
        OpenIdFederationStatementValidationContext $context
    ): OpenIdFederationTrustChainValidationResult;
}
