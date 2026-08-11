<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Formats\SdJwtVc;

final readonly class SdJwtVcCredentialStatusAssessment
{
    /**
     * @param list<string> $warnings
     */
    public function __construct(
        private bool $valid,
        private bool $revoked,
        private bool $suspended,
        private array $warnings = []
    ) {
    }

    public function valid(): bool
    {
        return $this->valid
            && !$this->revoked
            && !$this->suspended;
    }

    public function revoked(): bool
    {
        return $this->revoked;
    }

    public function suspended(): bool
    {
        return $this->suspended;
    }

    /** @return list<string> */
    public function warnings(): array
    {
        return $this->warnings;
    }
}
