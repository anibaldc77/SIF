<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\Metadata\OAuthAuthorizationServerMetadataCacheEntry;
use Sif\Foundation\Security\OAuth\Metadata\OAuthIssuerIdentifier;

interface OAuthAuthorizationServerMetadataCacheInterface
{
    public function get(
        OAuthIssuerIdentifier $issuer
    ): ?OAuthAuthorizationServerMetadataCacheEntry;

    public function put(
        OAuthIssuerIdentifier $issuer,
        OAuthAuthorizationServerMetadataCacheEntry $entry
    ): void;

    public function forget(
        OAuthIssuerIdentifier $issuer
    ): void;
}
