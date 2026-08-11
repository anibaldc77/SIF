<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Formats\SdJwtVc;

final readonly class SdJwtVcKeyBindingAssessment
{
    /**
     * @param list<string> $violations
     * @param list<string> $warnings
     */
    public function __construct(
        private bool $valid,
        private bool $audienceValid,
        private bool $nonceValid,
        private bool $holderKeyValid,
        private array $violations = [],
        private array $warnings = []
    ) {
    }

    public function valid(): bool
    {
        return $this->valid
            && $this->audienceValid
            && $this->nonceValid
            && $this->holderKeyValid;
    }

    public function audienceValid(): bool
    {
        return $this->audienceValid;
    }

    public function nonceValid(): bool
    {
        return $this->nonceValid;
    }

    public function holderKeyValid(): bool
    {
        return $this->holderKeyValid;
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
