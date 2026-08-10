<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\Metadata;

use InvalidArgumentException;

final readonly class OAuthClientRegistrationManagementAuthorization
{
    public function __construct(
        private string $clientId,
        private string $registrationAccessTokenReference
    ) {
        if (
            trim($this->clientId) === ''
            || trim($this->registrationAccessTokenReference) === ''
        ) {
            throw new InvalidArgumentException(
                'OAuth client registration management authorization is invalid.'
            );
        }
    }

    public function clientId(): string
    {
        return $this->clientId;
    }

    public function registrationAccessTokenReference(): string
    {
        return $this->registrationAccessTokenReference;
    }
}
