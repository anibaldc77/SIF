<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Oidc\OidcProviderMetadata;

interface OidcProviderMetadataProviderInterface
{
    public function get(): OidcProviderMetadata;

    public function refresh(): OidcProviderMetadata;
}
