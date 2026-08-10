<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\Metadata;

use DateTimeImmutable;

final readonly class OAuthMetadataResolutionContext
{
    public function __construct(
        private OAuthIssuerIdentifier $issuer,
        private DateTimeImmutable $requestedAt,
        private bool $allowCached = true,
        private bool $requireFresh = false
    ) {
    }

    public function issuer(): OAuthIssuerIdentifier
    {
        return $this->issuer;
    }

    public function requestedAt(): DateTimeImmutable
    {
        return $this->requestedAt;
    }

    public function allowCached(): bool
    {
        return $this->allowCached;
    }

    public function requireFresh(): bool
    {
        return $this->requireFresh;
    }
}
