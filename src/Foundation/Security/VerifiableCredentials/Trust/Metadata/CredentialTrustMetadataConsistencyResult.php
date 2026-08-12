<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Trust\Metadata;

final readonly class CredentialTrustMetadataConsistencyResult
{
    /**
     * @param list<string> $violations
     * @param list<string> $warnings
     */
    public function __construct(
        private bool $consistent,
        private array $violations = [],
        private array $warnings = []
    ) {
    }

    public function consistent(): bool
    {
        return $this->consistent && $this->violations === [];
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
