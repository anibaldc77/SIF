<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\WebAuthn;

use InvalidArgumentException;

final readonly class WebAuthnRequestOptions
{
    /**
     * @param list<WebAuthnCredentialDescriptor> $allowCredentials
     * @param list<string> $hints
     */
    public function __construct(
        private string $challenge,
        private string $relyingPartyId,
        private int $timeoutMilliseconds = 60000,
        private array $allowCredentials = [],
        private ?string $userVerification = null,
        private array $hints = []
    ) {
        if (
            trim($this->challenge) === ''
            || trim($this->relyingPartyId) === ''
            || $this->timeoutMilliseconds <= 0
        ) {
            throw new InvalidArgumentException(
                'WebAuthn request options are invalid.'
            );
        }
    }

    public function challenge(): string
    {
        return $this->challenge;
    }

    public function relyingPartyId(): string
    {
        return $this->relyingPartyId;
    }

    public function timeoutMilliseconds(): int
    {
        return $this->timeoutMilliseconds;
    }

    /**
     * @return list<WebAuthnCredentialDescriptor>
     */
    public function allowCredentials(): array
    {
        return $this->allowCredentials;
    }

    public function userVerification(): ?string
    {
        return $this->userVerification;
    }

    /**
     * @return list<string>
     */
    public function hints(): array
    {
        return $this->hints;
    }
}
