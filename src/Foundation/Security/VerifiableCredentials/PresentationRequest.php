<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials;

use InvalidArgumentException;

final readonly class PresentationRequest
{
    /**
     * @param list<string> $requestedCredentialTypes
     * @param list<string> $requestedClaims
     */
    public function __construct(
        private string $requestId,
        private string $audience,
        private string $nonce,
        private array $requestedCredentialTypes = [],
        private array $requestedClaims = [],
        private ?string $state = null
    ) {
        if (
            trim($this->requestId) === ''
            || trim($this->audience) === ''
            || trim($this->nonce) === ''
        ) {
            throw new InvalidArgumentException(
                'Presentation request is invalid.'
            );
        }
    }

    public function requestId(): string
    {
        return $this->requestId;
    }

    public function audience(): string
    {
        return $this->audience;
    }

    public function nonce(): string
    {
        return $this->nonce;
    }

    /**
     * @return list<string>
     */
    public function requestedCredentialTypes(): array
    {
        return $this->requestedCredentialTypes;
    }

    /**
     * @return list<string>
     */
    public function requestedClaims(): array
    {
        return $this->requestedClaims;
    }

    public function state(): ?string
    {
        return $this->state;
    }
}
