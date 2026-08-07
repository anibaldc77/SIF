<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Saml\SamlIdentityProviderMetadata;

interface SamlIdentityProviderMetadataProviderInterface
{
    public function get(): SamlIdentityProviderMetadata;

    public function refresh(): SamlIdentityProviderMetadata;
}
