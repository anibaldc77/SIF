<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationEntityConfiguration;

interface OpenIdFederationEntityConfigurationResolverInterface
{
    public function resolve(
        string $entityId
    ): OpenIdFederationEntityConfiguration;
}
