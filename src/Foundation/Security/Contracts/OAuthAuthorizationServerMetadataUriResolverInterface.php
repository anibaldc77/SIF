<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\Metadata\OAuthIssuerIdentifier;

interface OAuthAuthorizationServerMetadataUriResolverInterface
{
    public function resolve(
        OAuthIssuerIdentifier $issuer
    ): string;
}
