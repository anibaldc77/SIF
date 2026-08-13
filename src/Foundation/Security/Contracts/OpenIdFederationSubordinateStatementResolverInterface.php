<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationSubordinateStatement;

interface OpenIdFederationSubordinateStatementResolverInterface
{
    public function resolve(
        string $superiorEntityId,
        string $subordinateEntityId
    ): OpenIdFederationSubordinateStatement;
}
