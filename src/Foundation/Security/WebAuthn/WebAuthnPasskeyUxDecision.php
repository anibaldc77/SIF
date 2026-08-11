<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\WebAuthn;

final readonly class WebAuthnPasskeyUxDecision
{
    /**
     * @param list<string> $preferredCredentialIds
     * @param list<string> $warnings
     */
    public function __construct(
        private bool $allowConditionalMediation,
        private bool $allowUsernamelessFlow,
        private array $preferredCredentialIds = [],
        private array $warnings = []
    ) {
    }

    public function allowConditionalMediation(): bool
    {
        return $this->allowConditionalMediation;
    }

    public function allowUsernamelessFlow(): bool
    {
        return $this->allowUsernamelessFlow;
    }

    /** @return list<string> */
    public function preferredCredentialIds(): array
    {
        return $this->preferredCredentialIds;
    }

    /** @return list<string> */
    public function warnings(): array
    {
        return $this->warnings;
    }
}
