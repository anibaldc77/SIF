<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\Metadata\OAuthProtectedResourceMetadata;

interface OAuthProtectedResourceMetadataProviderInterface
{
    public function metadataFor(
        string $resource
    ): OAuthProtectedResourceMetadata;
}
