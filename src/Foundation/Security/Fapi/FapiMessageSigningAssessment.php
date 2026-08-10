<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Fapi;

final readonly class FapiMessageSigningAssessment
{
    /**
     * @param list<string> $violations
     */
    public function __construct(
        private bool $compliant,
        private array $violations = []
    ) {
    }

    public function compliant(): bool
    {
        return $this->compliant;
    }

    /**
     * @return list<string>
     */
    public function violations(): array
    {
        return $this->violations;
    }
}
