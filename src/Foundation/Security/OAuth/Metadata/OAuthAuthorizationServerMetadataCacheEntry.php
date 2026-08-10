<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\Metadata;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class OAuthAuthorizationServerMetadataCacheEntry
{
    public function __construct(
        private OAuthAuthorizationServerMetadata $metadata,
        private DateTimeImmutable $storedAt,
        private DateTimeImmutable $expiresAt
    ) {
        if ($this->expiresAt <= $this->storedAt) {
            throw new InvalidArgumentException(
                'OAuth metadata cache entry expiration is invalid.'
            );
        }
    }

    public function metadata(): OAuthAuthorizationServerMetadata
    {
        return $this->metadata;
    }

    public function storedAt(): DateTimeImmutable
    {
        return $this->storedAt;
    }

    public function expiresAt(): DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function expiredAt(DateTimeImmutable $instant): bool
    {
        return $instant >= $this->expiresAt;
    }
}
