<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Trust\Product;

final readonly class CredentialTrustProductReadinessReport
{
    /**
     * @param list<string> $blockingIssues
     * @param list<string> $warnings
     */
    public function __construct(
        private bool $ready,
        private array $blockingIssues = [],
        private array $warnings = []
    ) {
    }

    public function ready(): bool
    {
        return $this->ready && $this->blockingIssues === [];
    }

    /** @return list<string> */
    public function blockingIssues(): array
    {
        return $this->blockingIssues;
    }

    /** @return list<string> */
    public function warnings(): array
    {
        return $this->warnings;
    }
}
