<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp;

final readonly class OpenId4VpPresentationAssessment
{
    /**
     * @param list<string> $violations
     * @param list<string> $warnings
     */
    public function __construct(
        private bool $valid,
        private bool $nonceValid,
        private bool $verifierBound,
        private bool $presentationQuerySatisfied,
        private array $violations = [],
        private array $warnings = []
    ) {
    }

    public function valid(): bool
    {
        return $this->valid
            && $this->nonceValid
            && $this->verifierBound
            && $this->presentationQuerySatisfied;
    }

    public function nonceValid(): bool
    {
        return $this->nonceValid;
    }

    public function verifierBound(): bool
    {
        return $this->verifierBound;
    }

    public function presentationQuerySatisfied(): bool
    {
        return $this->presentationQuerySatisfied;
    }

    /**
     * @return list<string>
     */
    public function violations(): array
    {
        return $this->violations;
    }

    /**
     * @return list<string>
     */
    public function warnings(): array
    {
        return $this->warnings;
    }
}
