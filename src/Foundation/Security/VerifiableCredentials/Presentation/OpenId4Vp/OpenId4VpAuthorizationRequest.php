<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp;

use InvalidArgumentException;

final readonly class OpenId4VpAuthorizationRequest
{
    /**
     * @param array<string, mixed> $presentationQuery
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        private string $clientId,
        private string $nonce,
        private string $responseType = 'vp_token',
        private ?string $responseMode = null,
        private ?string $responseUri = null,
        private array $presentationQuery = [],
        private array $metadata = []
    ) {
        if (
            trim($this->clientId) === ''
            || trim($this->nonce) === ''
            || trim($this->responseType) === ''
        ) {
            throw new InvalidArgumentException(
                'OpenID4VP authorization request is invalid.'
            );
        }
    }

    public function clientId(): string
    {
        return $this->clientId;
    }

    public function nonce(): string
    {
        return $this->nonce;
    }

    public function responseType(): string
    {
        return $this->responseType;
    }

    public function responseMode(): ?string
    {
        return $this->responseMode;
    }

    public function responseUri(): ?string
    {
        return $this->responseUri;
    }

    /**
     * @return array<string, mixed>
     */
    public function presentationQuery(): array
    {
        return $this->presentationQuery;
    }

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }
}
