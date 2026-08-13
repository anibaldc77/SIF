<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OpenIdFederation\Protocol\OpenIdFederationListRequest;
use Sif\Foundation\Security\OpenIdFederation\Protocol\OpenIdFederationListResponse;

interface OpenIdFederationListProtocolInterface
{
    public function list(
        OpenIdFederationListRequest $request
    ): OpenIdFederationListResponse;
}
