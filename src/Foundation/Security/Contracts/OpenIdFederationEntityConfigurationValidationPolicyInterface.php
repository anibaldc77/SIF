<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationEntityConfiguration;
use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationStatementValidationContext;
use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationStatementValidationResult;

interface OpenIdFederationEntityConfigurationValidationPolicyInterface
{
    public function validate(
        OpenIdFederationEntityConfiguration $configuration,
        OpenIdFederationStatementValidationContext $context
    ): OpenIdFederationStatementValidationResult;
}
