<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\WebAuthn;

final readonly class WebAuthnRiskAssessment
{
    /**
     * @param list<string> $violations
     * @param list<string> $warnings
     */
    public function __construct(
        private bool $acceptable,
        private bool $stepUpRequired,
        private array $violations = [],
        private array $warnings = []
    ) {
    }

    public function acceptable(): bool { return $this->acceptable; }
    public function stepUpRequired(): bool { return $this->stepUpRequired; }
    /** @return list<string> */
    public function violations(): array { return $this->violations; }
    /** @return list<string> */
    public function warnings(): array { return $this->warnings; }
}
