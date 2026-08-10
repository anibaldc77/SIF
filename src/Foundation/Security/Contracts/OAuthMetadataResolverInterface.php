<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\Metadata\OAuthAuthorizationServerMetadata;
use Sif\Foundation\Security\OAuth\Metadata\OAuthMetadataResolutionContext;
use Sif\Foundation\Security\OAuth\Metadata\OAuthMetadataResolutionResult;

interface OAuthMetadataResolverInterface
{
    public function resolve(
        string $issuer
    ): OAuthAuthorizationServerMetadata;

    public function resolveWithContext(
        OAuthMetadataResolutionContext $context
    ): OAuthMetadataResolutionResult;
}
