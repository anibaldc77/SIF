<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\Metadata;

use DateTimeImmutable;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClient;

final readonly class OAuthClientRegistrationRecord
{
    public function __construct(
        private OAuthClient $client,
        private OAuthClientRegistrationMetadata $metadata,
        private DateTimeImmutable $registeredAt,
        private DateTimeImmutable $updatedAt,
        private bool $active = true,
        private ?string $registrationClientUri = null,
        private ?string $registrationAccessTokenReference = null
    ) {
    }

    public function client(): OAuthClient
    {
        return $this->client;
    }

    public function metadata(): OAuthClientRegistrationMetadata
    {
        return $this->metadata;
    }

    public function registeredAt(): DateTimeImmutable
    {
        return $this->registeredAt;
    }

    public function updatedAt(): DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function active(): bool
    {
        return $this->active;
    }

    public function registrationClientUri(): ?string
    {
        return $this->registrationClientUri;
    }

    public function registrationAccessTokenReference(): ?string
    {
        return $this->registrationAccessTokenReference;
    }
}
