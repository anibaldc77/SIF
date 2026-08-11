<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Formats\IsoMdoc;

final readonly class IsoMdocIssuerAuthenticationAssessment
{
    /**
     * @param list<string> $violations
     * @param list<string> $warnings
     */
    public function __construct(
        private bool $valid,
        private bool $signatureValid,
        private bool $certificatePathTrusted,
        private bool $msoValid,
        private array $violations = [],
        private array $warnings = []
    ) {
    }

    public function valid(): bool
    {
        return $this->valid
            && $this->signatureValid
            && $this->certificatePathTrusted
            && $this->msoValid;
    }

    public function signatureValid(): bool
    {
        return $this->signatureValid;
    }

    public function certificatePathTrusted(): bool
    {
        return $this->certificatePathTrusted;
    }

    public function msoValid(): bool
    {
        return $this->msoValid;
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
