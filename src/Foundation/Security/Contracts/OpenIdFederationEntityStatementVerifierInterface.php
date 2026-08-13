<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationEntityStatement;
use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationStatementValidationResult;

interface OpenIdFederationEntityStatementVerifierInterface
{
    public function verify(
        OpenIdFederationEntityStatement $statement
    ): OpenIdFederationStatementValidationResult;
}
