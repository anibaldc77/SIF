<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OpenIdFederation\TrustChain;

final readonly class OpenIdFederationTrustChainValidationResult
{
    /**
     * @param list<string> $violations
     * @param list<string> $warnings
     */
    public function __construct(
        private bool $valid,
        private array $violations = [],
        private array $warnings = []
    ) {
    }

    public function valid(): bool
    {
        return $this->valid && $this->violations === [];
    }

    /** @return list<string> */
    public function violations(): array
    {
        return $this->violations;
    }

    /** @return list<string> */
    public function warnings(): array
    {
        return $this->warnings;
    }
}
