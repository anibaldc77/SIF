<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OAuth\Metadata\OAuthAuthorizationServerMetadata;

interface OAuthAuthorizationServerMetadataSerializerInterface
{
    /**
     * @return array<string, mixed>
     */
    public function serialize(
        OAuthAuthorizationServerMetadata $metadata
    ): array;
}
