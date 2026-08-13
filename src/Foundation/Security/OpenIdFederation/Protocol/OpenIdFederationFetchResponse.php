<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OpenIdFederation\Protocol;

use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationSubordinateStatement;

final readonly class OpenIdFederationFetchResponse
{
    public function __construct(
        private OpenIdFederationSubordinateStatement $statement
    ) {
    }

    public function statement(): OpenIdFederationSubordinateStatement
    {
        return $this->statement;
    }
}
