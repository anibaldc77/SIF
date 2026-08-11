<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Formats\IsoMdoc;

final readonly class IsoMdocDeviceAuthenticationAssessment
{
    /**
     * @param list<string> $violations
     * @param list<string> $warnings
     */
    public function __construct(
        private bool $valid,
        private bool $deviceKeyValid,
        private bool $sessionTranscriptValid,
        private bool $documentTypeBound,
        private array $violations = [],
        private array $warnings = []
    ) {
    }

    public function valid(): bool
    {
        return $this->valid
            && $this->deviceKeyValid
            && $this->sessionTranscriptValid
            && $this->documentTypeBound;
    }

    public function deviceKeyValid(): bool
    {
        return $this->deviceKeyValid;
    }

    public function sessionTranscriptValid(): bool
    {
        return $this->sessionTranscriptValid;
    }

    public function documentTypeBound(): bool
    {
        return $this->documentTypeBound;
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
