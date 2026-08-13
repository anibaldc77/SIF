<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OpenIdFederation\TrustMarks\OpenIdFederationTrustMark;

interface OpenIdFederationTrustMarkResolverInterface
{
    public function resolve(
        string $issuerEntityId,
        string $subjectEntityId,
        string $trustMarkId
    ): OpenIdFederationTrustMark;
}
