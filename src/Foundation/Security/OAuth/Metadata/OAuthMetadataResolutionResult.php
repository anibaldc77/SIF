<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\Metadata;

final readonly class OAuthMetadataResolutionResult
{
    public function __construct(
        private OAuthAuthorizationServerMetadata $metadata,
        private bool $fromCache,
        private bool $fresh,
        private string $discoveryUri
    ) {
    }

    public function metadata(): OAuthAuthorizationServerMetadata
    {
        return $this->metadata;
    }

    public function fromCache(): bool
    {
        return $this->fromCache;
    }

    public function fresh(): bool
    {
        return $this->fresh;
    }

    public function discoveryUri(): string
    {
        return $this->discoveryUri;
    }
}
