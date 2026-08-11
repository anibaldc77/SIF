<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp;

final readonly class OpenId4VpDigitalCredentialsAssessment
{
    /**
     * @param list<string> $violations
     * @param list<string> $warnings
     */
    public function __construct(
        private bool $valid,
        private bool $protocolSupported,
        private bool $originValid,
        private bool $userMediationSatisfied,
        private array $violations = [],
        private array $warnings = []
    ) {
    }

    public function valid(): bool
    {
        return $this->valid
            && $this->protocolSupported
            && $this->originValid
            && $this->userMediationSatisfied;
    }

    public function protocolSupported(): bool
    {
        return $this->protocolSupported;
    }

    public function originValid(): bool
    {
        return $this->originValid;
    }

    public function userMediationSatisfied(): bool
    {
        return $this->userMediationSatisfied;
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
