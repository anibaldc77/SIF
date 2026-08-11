<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Formats\SdJwtVc;

final readonly class SdJwtVcIssuerTrustAssessment
{
    /**
     * @param list<string> $violations
     * @param list<string> $warnings
     */
    public function __construct(
        private bool $trusted,
        private bool $identifierValid,
        private bool $keyMaterialTrusted,
        private array $violations = [],
        private array $warnings = []
    ) {
    }

    public function trusted(): bool
    {
        return $this->trusted
            && $this->identifierValid
            && $this->keyMaterialTrusted;
    }

    public function identifierValid(): bool
    {
        return $this->identifierValid;
    }

    public function keyMaterialTrusted(): bool
    {
        return $this->keyMaterialTrusted;
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
