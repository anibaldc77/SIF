<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OpenIdFederation;

use InvalidArgumentException;

final readonly class OpenIdFederationEntityConfiguration
{
    /**
     * @param array<string, mixed> $federationEntityMetadata
     */
    public function __construct(
        private OpenIdFederationEntityStatement $statement,
        private array $federationEntityMetadata = []
    ) {
        if (
            $this->statement->kind()
            !== OpenIdFederationEntityStatementKind::EntityConfiguration
        ) {
            throw new InvalidArgumentException(
                'OpenID Federation Entity Configuration requires an entity-configuration statement.'
            );
        }
    }

    public function statement(): OpenIdFederationEntityStatement
    {
        return $this->statement;
    }

    /** @return array<string, mixed> */
    public function federationEntityMetadata(): array
    {
        return $this->federationEntityMetadata;
    }

    public function entityId(): string
    {
        return $this->statement->subject();
    }
}
