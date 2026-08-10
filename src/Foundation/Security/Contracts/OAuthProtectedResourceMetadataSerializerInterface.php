<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\Metadata\OAuthProtectedResourceMetadata;

interface OAuthProtectedResourceMetadataSerializerInterface
{
    /**
     * @return array<string, mixed>
     */
    public function serialize(
        OAuthProtectedResourceMetadata $metadata
    ): array;
}
