<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Recovery\Protection;

use Sif\Foundation\Security\IdentityProvider\IdentityLookupKey;
use Sif\Foundation\Security\IdentityProvider\IdentityProviderId;
use Sif\Foundation\Security\Recovery\RecoveryChallengePurpose;

final readonly class RecoveryRequestKey
{
    public function __construct(
        private IdentityProviderId $providerId,
        private IdentityLookupKey $lookupKey,
        private RecoveryChallengePurpose $purpose
    ) {
    }

    public function fingerprint(): string
    {
        return hash('sha256', implode('|', [
            $this->providerId->value(),
            $this->lookupKey->value(),
            $this->purpose->value,
        ]));
    }
}
