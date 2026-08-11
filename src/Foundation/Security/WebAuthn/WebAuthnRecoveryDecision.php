<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\WebAuthn;

final readonly class WebAuthnRecoveryDecision
{
    /**
     * @param list<string> $credentialsToRetire
     * @param list<string> $requiredActions
     * @param list<string> $warnings
     */
    public function __construct(
        private bool $allowed,
        private array $credentialsToRetire = [],
        private array $requiredActions = [],
        private array $warnings = []
    ) {
    }

    public function allowed(): bool
    {
        return $this->allowed;
    }

    /** @return list<string> */
    public function credentialsToRetire(): array
    {
        return $this->credentialsToRetire;
    }

    /** @return list<string> */
    public function requiredActions(): array
    {
        return $this->requiredActions;
    }

    /** @return list<string> */
    public function warnings(): array
    {
        return $this->warnings;
    }
}
