<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp;

final readonly class OpenId4VpPresentationQueryAssessment
{
    /**
     * @param list<string> $missingRequirementIds
     * @param list<string> $warnings
     */
    public function __construct(
        private bool $satisfied,
        private array $missingRequirementIds = [],
        private array $warnings = []
    ) {
    }

    public function satisfied(): bool
    {
        return $this->satisfied;
    }

    /**
     * @return list<string>
     */
    public function missingRequirementIds(): array
    {
        return $this->missingRequirementIds;
    }

    /**
     * @return list<string>
     */
    public function warnings(): array
    {
        return $this->warnings;
    }
}
