<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\OAuth\Metadata;

use InvalidArgumentException;

final readonly class OAuthClientRegistrationMetadata
{
    /**
     * @param list<string> $redirectUris
     * @param list<string> $grantTypes
     * @param list<string> $responseTypes
     */
    public function __construct(
        private string $clientName,
        private array $redirectUris,
        private array $grantTypes = [],
        private array $responseTypes = []
    ) {
        if (
            trim($this->clientName) === ''
            || $this->redirectUris === []
        ) {
            throw new InvalidArgumentException(
                'OAuth client registration metadata is invalid.'
            );
        }
    }

    public function clientName(): string
    {
        return $this->clientName;
    }

    /**
     * @return list<string>
     */
    public function redirectUris(): array
    {
        return $this->redirectUris;
    }

    /**
     * @return list<string>
     */
    public function grantTypes(): array
    {
        return $this->grantTypes;
    }

    /**
     * @return list<string>
     */
    public function responseTypes(): array
    {
        return $this->responseTypes;
    }
}
