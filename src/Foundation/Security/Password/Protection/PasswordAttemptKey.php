<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Password\Protection;

use Sif\Foundation\Security\IdentityProvider\IdentityLookupKey;
use Sif\Foundation\Security\IdentityProvider\IdentityProviderId;

final readonly class PasswordAttemptKey
{
    public function __construct(
        private IdentityProviderId $providerId,
        private IdentityLookupKey $lookupKey
    ) {
    }

    public function providerId(): IdentityProviderId
    {
        return $this->providerId;
    }

    public function lookupKey(): IdentityLookupKey
    {
        return $this->lookupKey;
    }

    public function fingerprint(): string
    {
        return hash('sha256', $this->providerId->value() . "\0" . $this->lookupKey->value());
    }

    /** @return array{provider_id: string, lookup_fingerprint: string} */
    public function __debugInfo(): array
    {
        return [
            'provider_id' => $this->providerId->value(),
            'lookup_fingerprint' => $this->fingerprint(),
        ];
    }
}
