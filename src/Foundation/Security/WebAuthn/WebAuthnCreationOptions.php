<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\WebAuthn;

use InvalidArgumentException;

final readonly class WebAuthnCreationOptions
{
    /**
     * @param list<int> $publicKeyCredentialParameters
     * @param list<WebAuthnCredentialDescriptor> $excludeCredentials
     * @param list<string> $hints
     */
    public function __construct(
        private WebAuthnRelyingParty $relyingParty,
        private WebAuthnUserEntity $user,
        private string $challenge,
        private array $publicKeyCredentialParameters,
        private int $timeoutMilliseconds = 60000,
        private ?WebAuthnAuthenticatorSelection $authenticatorSelection = null,
        private array $excludeCredentials = [],
        private ?string $attestation = null,
        private array $hints = []
    ) {
        if (
            trim($this->challenge) === ''
            || $this->publicKeyCredentialParameters === []
            || $this->timeoutMilliseconds <= 0
        ) {
            throw new InvalidArgumentException('WebAuthn creation options are invalid.');
        }
    }

    public function relyingParty(): WebAuthnRelyingParty { return $this->relyingParty; }
    public function user(): WebAuthnUserEntity { return $this->user; }
    public function challenge(): string { return $this->challenge; }

    /** @return list<int> */
    public function publicKeyCredentialParameters(): array { return $this->publicKeyCredentialParameters; }

    public function timeoutMilliseconds(): int { return $this->timeoutMilliseconds; }
    public function authenticatorSelection(): ?WebAuthnAuthenticatorSelection { return $this->authenticatorSelection; }

    /** @return list<WebAuthnCredentialDescriptor> */
    public function excludeCredentials(): array { return $this->excludeCredentials; }

    public function attestation(): ?string { return $this->attestation; }

    /** @return list<string> */
    public function hints(): array { return $this->hints; }
}
