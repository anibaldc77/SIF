<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OpenIdFederation\TrustChain\OpenIdFederationTrustChain;

interface OpenIdFederationTrustChainCollectorInterface
{
    /** @param list<string> $trustAnchorEntityIds */
    public function collect(
        string $subjectEntityId,
        array $trustAnchorEntityIds
    ): OpenIdFederationTrustChain;
}
