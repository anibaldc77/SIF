<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\SharedSignals;

final readonly class SharedSignalsProductCapabilities
{
    public function __construct(
        private bool $securityEventTokens = true,
        private bool $subjectIdentifiers = true,
        private bool $streamDelivery = true,
        private bool $caep = true,
        private bool $risc = true,
        private bool $continuousAccessReactions = true,
        private bool $provisioningInteroperability = true
    ) {
    }

    public function securityEventTokens(): bool { return $this->securityEventTokens; }
    public function subjectIdentifiers(): bool { return $this->subjectIdentifiers; }
    public function streamDelivery(): bool { return $this->streamDelivery; }
    public function caep(): bool { return $this->caep; }
    public function risc(): bool { return $this->risc; }
    public function continuousAccessReactions(): bool { return $this->continuousAccessReactions; }
    public function provisioningInteroperability(): bool { return $this->provisioningInteroperability; }

    /** @return array<string, bool> */
    public function toArray(): array
    {
        return [
            'security_event_tokens' => $this->securityEventTokens,
            'subject_identifiers' => $this->subjectIdentifiers,
            'stream_delivery' => $this->streamDelivery,
            'caep' => $this->caep,
            'risc' => $this->risc,
            'continuous_access_reactions' => $this->continuousAccessReactions,
            'provisioning_interoperability' => $this->provisioningInteroperability,
        ];
    }
}
