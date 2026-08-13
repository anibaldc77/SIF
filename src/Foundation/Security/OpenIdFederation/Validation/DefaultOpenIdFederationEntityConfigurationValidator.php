<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OpenIdFederation\Validation;

use Sif\Foundation\Security\Contracts\OpenIdFederationEntityConfigurationValidationPolicyInterface;
use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationEntityConfiguration;
use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationStatementValidationContext;
use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationStatementValidationResult;

final readonly class DefaultOpenIdFederationEntityConfigurationValidator implements OpenIdFederationEntityConfigurationValidationPolicyInterface
{
    public function validate(
        OpenIdFederationEntityConfiguration $configuration,
        OpenIdFederationStatementValidationContext $context
    ): OpenIdFederationStatementValidationResult {
        $statement = $configuration->statement();
        $violations = [];

        if (!$statement->isSelfIssued()) {
            $violations[] = 'entity_configuration_not_self_issued';
        }

        if ($statement->expiresAt() <= $context->evaluatedAt()) {
            $violations[] = 'entity_statement_expired';
        }

        if ($statement->issuedAt() > $context->evaluatedAt()) {
            $violations[] = 'entity_statement_issued_in_future';
        }

        if ($statement->metadataPolicy() !== []) {
            $violations[] = 'metadata_policy_not_allowed_in_entity_configuration';
        }

        if (
            $context->expectedEntityId() !== null
            && $statement->subject() !== $context->expectedEntityId()
        ) {
            $violations[] = 'unexpected_entity_identifier';
        }

        return new OpenIdFederationStatementValidationResult(
            $violations === [],
            $violations
        );
    }
}
