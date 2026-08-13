<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationStatementValidationContext;
use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationStatementValidationResult;
use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationSubordinateStatement;

interface OpenIdFederationSubordinateStatementValidationPolicyInterface
{
    public function validate(
        OpenIdFederationSubordinateStatement $statement,
        OpenIdFederationStatementValidationContext $context
    ): OpenIdFederationStatementValidationResult;
}
