<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\WebAuthn;

use InvalidArgumentException;

final readonly class WebAuthnCredentialDescriptor
{
    /** @param list<string> $transports */
    public function __construct(
        private string $credentialId,
        private array $transports = []
    ) {
        if (trim($this->credentialId) === '') {
            throw new InvalidArgumentException('WebAuthn credential descriptor is invalid.');
        }
    }

    public function credentialId(): string { return $this->credentialId; }

    /** @return list<string> */
    public function transports(): array { return $this->transports; }
}
