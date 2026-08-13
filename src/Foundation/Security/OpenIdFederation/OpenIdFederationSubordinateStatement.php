<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OpenIdFederation;

use InvalidArgumentException;

final readonly class OpenIdFederationSubordinateStatement
{
    public function __construct(
        private OpenIdFederationEntityStatement $statement
    ) {
        if (
            $this->statement->kind()
            !== OpenIdFederationEntityStatementKind::SubordinateStatement
        ) {
            throw new InvalidArgumentException(
                'OpenID Federation Subordinate Statement requires a subordinate-statement kind.'
            );
        }

        if ($this->statement->isSelfIssued()) {
            throw new InvalidArgumentException(
                'OpenID Federation Subordinate Statement cannot be self-issued.'
            );
        }
    }

    public function statement(): OpenIdFederationEntityStatement
    {
        return $this->statement;
    }

    public function superiorEntityId(): string
    {
        return $this->statement->issuer();
    }

    public function subordinateEntityId(): string
    {
        return $this->statement->subject();
    }
}
