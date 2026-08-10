<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\Metadata;

use InvalidArgumentException;

final readonly class OAuthClientRegistrationUpdate
{
    public function __construct(
        private string $clientId,
        private OAuthClientRegistrationMetadata $metadata
    ) {
        if (trim($this->clientId) === '') {
            throw new InvalidArgumentException(
                'OAuth client registration update client id is invalid.'
            );
        }
    }

    public function clientId(): string
    {
        return $this->clientId;
    }

    public function metadata(): OAuthClientRegistrationMetadata
    {
        return $this->metadata;
    }
}
