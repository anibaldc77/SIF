<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\Metadata;

use DateTimeImmutable;
use Sif\Foundation\Security\OAuth\AuthorizationServer\OAuthClient;

final readonly class OAuthDynamicClientRegistrationResult
{
    /**
     * @param list<OAuthClientRegistrationCredential> $credentials
     */
    public function __construct(
        private OAuthClient $client,
        private OAuthClientRegistrationMetadata $registeredMetadata,
        private DateTimeImmutable $registeredAt,
        private array $credentials = [],
        private ?string $registrationClientUri = null,
        private ?string $registrationAccessTokenReference = null
    ) {
    }

    public function client(): OAuthClient
    {
        return $this->client;
    }

    public function registeredMetadata(): OAuthClientRegistrationMetadata
    {
        return $this->registeredMetadata;
    }

    public function registeredAt(): DateTimeImmutable
    {
        return $this->registeredAt;
    }

    /**
     * @return list<OAuthClientRegistrationCredential>
     */
    public function credentials(): array
    {
        return $this->credentials;
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
