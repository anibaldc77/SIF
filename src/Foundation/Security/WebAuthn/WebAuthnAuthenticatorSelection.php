<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\WebAuthn;

final readonly class WebAuthnAuthenticatorSelection
{
    public function __construct(
        private ?string $authenticatorAttachment = null,
        private ?string $residentKey = null,
        private ?string $userVerification = null
    ) {
    }

    public function authenticatorAttachment(): ?string { return $this->authenticatorAttachment; }
    public function residentKey(): ?string { return $this->residentKey; }
    public function userVerification(): ?string { return $this->userVerification; }
}
