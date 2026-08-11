<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\WebAuthn;

use InvalidArgumentException;

final readonly class WebAuthnCredential
{
    /**
     * @param list<string> $transports
     * @param array<string, mixed> $metadata
     */
    public function __construct(
        private string $credentialId,
        private string $userHandle,
        private string $relyingPartyId,
        private string $publicKey,
        private int $signatureCounter = 0,
        private array $transports = [],
        private array $metadata = []
    ) {
        if (
            trim($this->credentialId) === ''
            || trim($this->userHandle) === ''
            || trim($this->relyingPartyId) === ''
            || trim($this->publicKey) === ''
            || $this->signatureCounter < 0
        ) {
            throw new InvalidArgumentException(
                'WebAuthn credential is invalid.'
            );
        }
    }

    public function credentialId(): string
    {
        return $this->credentialId;
    }

    public function userHandle(): string
    {
        return $this->userHandle;
    }

    public function relyingPartyId(): string
    {
        return $this->relyingPartyId;
    }

    public function publicKey(): string
    {
        return $this->publicKey;
    }

    public function signatureCounter(): int
    {
        return $this->signatureCounter;
    }

    /**
     * @return list<string>
     */
    public function transports(): array
    {
        return $this->transports;
    }

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }
}
