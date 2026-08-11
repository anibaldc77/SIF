<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Status;

use DateTimeImmutable;

final readonly class CredentialStatusAssessment
{
    /**
     * @param list<string> $violations
     * @param list<string> $warnings
     */
    public function __construct(
        private bool $valid,
        private bool $revoked,
        private bool $suspended,
        private DateTimeImmutable $evaluatedAt,
        private ?DateTimeImmutable $sourceUpdatedAt = null,
        private array $violations = [],
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

    public function evaluatedAt(): DateTimeImmutable
    {
        return $this->evaluatedAt;
    }

    public function sourceUpdatedAt(): ?DateTimeImmutable
    {
        return $this->sourceUpdatedAt;
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
