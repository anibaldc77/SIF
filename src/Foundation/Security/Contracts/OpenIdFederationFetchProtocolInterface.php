<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OpenIdFederation\Protocol\OpenIdFederationFetchRequest;
use Sif\Foundation\Security\OpenIdFederation\Protocol\OpenIdFederationFetchResponse;

interface OpenIdFederationFetchProtocolInterface
{
    public function fetch(
        OpenIdFederationFetchRequest $request
    ): OpenIdFederationFetchResponse;
}
