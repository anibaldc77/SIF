<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OpenIdFederation\MetadataPolicy;

use InvalidArgumentException;

final readonly class OpenIdFederationMetadataPolicy
{
    /**
     * @param array<string, array<string, OpenIdFederationMetadataParameterPolicy>> $entityTypePolicies
     * @param list<string> $criticalOperators
     */
    public function __construct(
        private array $entityTypePolicies,
        private array $criticalOperators = []
    ) {
        if ($this->entityTypePolicies === []) {
            throw new InvalidArgumentException(
                'OpenID Federation metadata policy cannot be empty.'
            );
        }

        foreach ($this->criticalOperators as $operator) {
            if (trim($operator) === '') {
                throw new InvalidArgumentException(
                    'OpenID Federation critical metadata policy operator is invalid.'
                );
            }
        }
    }

    /**
     * @return array<string, array<string, OpenIdFederationMetadataParameterPolicy>>
     */
    public function entityTypePolicies(): array
    {
        return $this->entityTypePolicies;
    }

    /** @return list<string> */
    public function criticalOperators(): array
    {
        return $this->criticalOperators;
    }

    /**
     * @return array<string, OpenIdFederationMetadataParameterPolicy>
     */
    public function forEntityType(string $entityType): array
    {
        return $this->entityTypePolicies[$entityType] ?? [];
    }
}
