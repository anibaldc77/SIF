<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials;

use InvalidArgumentException;

final readonly class PresentationResponse
{
    public function __construct(
        private string $requestId,
        private VerifiablePresentation $presentation,
        private string $audience,
        private string $nonce,
        private ?string $state = null
    ) {
        if (
            trim($this->requestId) === ''
            || trim($this->audience) === ''
            || trim($this->nonce) === ''
        ) {
            throw new InvalidArgumentException(
                'Presentation response is invalid.'
            );
        }
    }

    public function requestId(): string
    {
        return $this->requestId;
    }

    public function presentation(): VerifiablePresentation
    {
        return $this->presentation;
    }

    public function audience(): string
    {
        return $this->audience;
    }

    public function nonce(): string
    {
        return $this->nonce;
    }

    public function state(): ?string
    {
        return $this->state;
    }
}
