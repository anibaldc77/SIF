<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials;

use InvalidArgumentException;

final readonly class IdentityAssuranceContext
{
    /**
     * @param list<string> $requiredEvidenceTypes
     * @param list<string> $requiredMethods
     */
    public function __construct(
        private IdentityAssuranceLevel $requiredLevel,
        private array $requiredEvidenceTypes = [],
        private array $requiredMethods = [],
        private bool $requireVerifiedClaims = true
    ) {
        if (
            $this->requiredEvidenceTypes === []
            && $this->requiredMethods === []
            && !$this->requireVerifiedClaims
        ) {
            throw new InvalidArgumentException(
                'Identity assurance context requires at least one criterion.'
            );
        }
    }

    public function requiredLevel(): IdentityAssuranceLevel
    {
        return $this->requiredLevel;
    }

    /** @return list<string> */
    public function requiredEvidenceTypes(): array
    {
        return $this->requiredEvidenceTypes;
    }

    /** @return list<string> */
    public function requiredMethods(): array
    {
        return $this->requiredMethods;
    }

    public function requireVerifiedClaims(): bool
    {
        return $this->requireVerifiedClaims;
    }
}
