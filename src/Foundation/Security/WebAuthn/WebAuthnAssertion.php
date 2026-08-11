<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\WebAuthn;

use InvalidArgumentException;

final readonly class WebAuthnAssertion
{
    public function __construct(
        private string $credentialId,
        private string $clientDataJson,
        private string $authenticatorData,
        private string $signature,
        private ?string $userHandle = null
    ) {
        if (
            trim($this->credentialId) === ''
            || trim($this->clientDataJson) === ''
            || trim($this->authenticatorData) === ''
            || trim($this->signature) === ''
        ) {
            throw new InvalidArgumentException(
                'WebAuthn assertion is invalid.'
            );
        }
    }

    public function credentialId(): string
    {
        return $this->credentialId;
    }

    public function clientDataJson(): string
    {
        return $this->clientDataJson;
    }

    public function authenticatorData(): string
    {
        return $this->authenticatorData;
    }

    public function signature(): string
    {
        return $this->signature;
    }

    public function userHandle(): ?string
    {
        return $this->userHandle;
    }
}
