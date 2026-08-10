<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials;

use InvalidArgumentException;

final readonly class IdentityAssuranceProfile
{
    /**
     * @param list<string> $acceptedEvidenceTypes
     * @param list<string> $acceptedMethods
     */
    public function __construct(
        private string $name,
        private IdentityAssuranceLevel $level,
        private array $acceptedEvidenceTypes = [],
        private array $acceptedMethods = []
    ) {
        if (trim($this->name) === '') {
            throw new InvalidArgumentException(
                'Identity assurance profile name is invalid.'
            );
        }
    }

    public function name(): string
    {
        return $this->name;
    }

    public function level(): IdentityAssuranceLevel
    {
        return $this->level;
    }

    /** @return list<string> */
    public function acceptedEvidenceTypes(): array
    {
        return $this->acceptedEvidenceTypes;
    }

    /** @return list<string> */
    public function acceptedMethods(): array
    {
        return $this->acceptedMethods;
    }
}
