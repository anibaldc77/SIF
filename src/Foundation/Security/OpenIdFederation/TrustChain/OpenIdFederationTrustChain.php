<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OpenIdFederation\TrustChain;

use InvalidArgumentException;
use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationEntityConfiguration;
use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationSubordinateStatement;

final readonly class OpenIdFederationTrustChain
{
    /** @param list<OpenIdFederationSubordinateStatement> $subordinateStatements */
    public function __construct(
        private OpenIdFederationEntityConfiguration $leafConfiguration,
        private array $subordinateStatements,
        private OpenIdFederationEntityConfiguration $trustAnchorConfiguration
    ) {
        if ($this->subordinateStatements === []) {
            throw new InvalidArgumentException(
                'OpenID Federation trust chain requires at least one subordinate statement.'
            );
        }
    }

    public function leafConfiguration(): OpenIdFederationEntityConfiguration
    {
        return $this->leafConfiguration;
    }

    /** @return list<OpenIdFederationSubordinateStatement> */
    public function subordinateStatements(): array
    {
        return $this->subordinateStatements;
    }

    public function trustAnchorConfiguration(): OpenIdFederationEntityConfiguration
    {
        return $this->trustAnchorConfiguration;
    }

    public function depth(): int
    {
        return count($this->subordinateStatements) + 1;
    }

    public function leafEntityId(): string
    {
        return $this->leafConfiguration->entityId();
    }

    public function trustAnchorEntityId(): string
    {
        return $this->trustAnchorConfiguration->entityId();
    }
}
