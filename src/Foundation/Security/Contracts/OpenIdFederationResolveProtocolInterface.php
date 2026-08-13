<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OpenIdFederation\Protocol\OpenIdFederationResolveRequest;
use Sif\Foundation\Security\OpenIdFederation\Protocol\OpenIdFederationResolveResponse;

interface OpenIdFederationResolveProtocolInterface
{
    public function resolve(
        OpenIdFederationResolveRequest $request
    ): OpenIdFederationResolveResponse;
}
