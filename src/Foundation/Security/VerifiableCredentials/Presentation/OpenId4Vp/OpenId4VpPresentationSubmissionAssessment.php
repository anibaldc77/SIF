<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp;

final readonly class OpenId4VpPresentationSubmissionAssessment
{
    /**
     * @param list<string> $missingDescriptorIds
     * @param list<string> $violations
     * @param list<string> $warnings
     */
    public function __construct(
        private bool $valid,
        private array $missingDescriptorIds = [],
        private array $violations = [],
        private array $warnings = []
    ) {
    }

    public function valid(): bool
    {
        return $this->valid && $this->missingDescriptorIds === [];
    }

    /**
     * @return list<string>
     */
    public function missingDescriptorIds(): array
    {
        return $this->missingDescriptorIds;
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
