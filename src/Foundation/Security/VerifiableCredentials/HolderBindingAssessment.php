<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials;

final readonly class HolderBindingAssessment
{
    /**
     * @param list<string> $violations
     */
    public function __construct(
        private bool $valid,
        private array $violations = []
    ) {
    }

    public function valid(): bool
    {
        return $this->valid;
    }

    /** @return list<string> */
    public function violations(): array
    {
        return $this->violations;
    }
}
