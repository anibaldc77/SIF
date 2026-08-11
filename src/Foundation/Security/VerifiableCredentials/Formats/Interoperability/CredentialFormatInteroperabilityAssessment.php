<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Formats\Interoperability;

final readonly class CredentialFormatInteroperabilityAssessment
{
    /**
     * @param list<string> $violations
     * @param list<string> $warnings
     */
    public function __construct(
        private bool $compatible,
        private bool $formatSupported,
        private bool $profileSupported,
        private bool $claimsCompatible,
        private array $violations = [],
        private array $warnings = []
    ) {
    }

    public function compatible(): bool
    {
        return $this->compatible
            && $this->formatSupported
            && $this->profileSupported
            && $this->claimsCompatible;
    }

    public function formatSupported(): bool
    {
        return $this->formatSupported;
    }

    public function profileSupported(): bool
    {
        return $this->profileSupported;
    }

    public function claimsCompatible(): bool
    {
        return $this->claimsCompatible;
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
