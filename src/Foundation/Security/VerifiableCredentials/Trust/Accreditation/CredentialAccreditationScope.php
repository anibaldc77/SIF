<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Trust\Accreditation;

use InvalidArgumentException;

final readonly class CredentialAccreditationScope
{
    /**
     * @param list<string> $credentialTypes
     * @param list<string> $jurisdictions
     * @param list<string> $constraints
     */
    public function __construct(
        private string $scopeId,
        private array $credentialTypes = [],
        private array $jurisdictions = [],
        private array $constraints = []
    ) {
        if (trim($this->scopeId) === '') {
            throw new InvalidArgumentException('Credential accreditation scope is invalid.');
        }
    }

    public function scopeId(): string
    {
        return $this->scopeId;
    }

    /** @return list<string> */
    public function credentialTypes(): array
    {
        return $this->credentialTypes;
    }

    /** @return list<string> */
    public function jurisdictions(): array
    {
        return $this->jurisdictions;
    }

    /** @return list<string> */
    public function constraints(): array
    {
        return $this->constraints;
    }
}
