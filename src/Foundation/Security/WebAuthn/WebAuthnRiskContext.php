<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\WebAuthn;

final readonly class WebAuthnRiskContext
{
    /**
     * @param list<string> $signals
     * @param list<string> $activeControls
     */
    public function __construct(
        private string $userId,
        private string $credentialId,
        private bool $highRisk,
        private array $signals = [],
        private array $activeControls = []
    ) {
    }

    public function userId(): string { return $this->userId; }
    public function credentialId(): string { return $this->credentialId; }
    public function highRisk(): bool { return $this->highRisk; }
    /** @return list<string> */
    public function signals(): array { return $this->signals; }
    /** @return list<string> */
    public function activeControls(): array { return $this->activeControls; }
}
