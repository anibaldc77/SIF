<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\WebAuthn;

final readonly class WebAuthnStepUpRequirement
{
    /**
     * @param list<string> $requiredFactors
     * @param list<string> $requiredControls
     */
    public function __construct(
        private bool $required,
        private array $requiredFactors = [],
        private array $requiredControls = []
    ) {
    }

    public function required(): bool { return $this->required; }
    /** @return list<string> */
    public function requiredFactors(): array { return $this->requiredFactors; }
    /** @return list<string> */
    public function requiredControls(): array { return $this->requiredControls; }
}
