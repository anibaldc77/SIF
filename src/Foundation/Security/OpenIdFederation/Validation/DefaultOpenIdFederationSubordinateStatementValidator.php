<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OpenIdFederation\Validation;

use Sif\Foundation\Security\Contracts\OpenIdFederationSubordinateStatementValidationPolicyInterface;
use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationStatementValidationContext;
use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationStatementValidationResult;
use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationSubordinateStatement;

final readonly class DefaultOpenIdFederationSubordinateStatementValidator implements OpenIdFederationSubordinateStatementValidationPolicyInterface
{
    public function validate(
        OpenIdFederationSubordinateStatement $statement,
        OpenIdFederationStatementValidationContext $context
    ): OpenIdFederationStatementValidationResult {
        $entityStatement = $statement->statement();
        $violations = [];

        if ($entityStatement->isSelfIssued()) {
            $violations[] = 'subordinate_statement_self_issued';
        }

        if ($entityStatement->expiresAt() <= $context->evaluatedAt()) {
            $violations[] = 'entity_statement_expired';
        }

        if ($entityStatement->issuedAt() > $context->evaluatedAt()) {
            $violations[] = 'entity_statement_issued_in_future';
        }

        if ($entityStatement->authorityHints() !== []) {
            $violations[] = 'authority_hints_not_allowed_in_subordinate_statement';
        }

        if (
            $context->expectedEntityId() !== null
            && $entityStatement->subject() !== $context->expectedEntityId()
        ) {
            $violations[] = 'unexpected_entity_identifier';
        }

        return new OpenIdFederationStatementValidationResult(
            $violations === [],
            $violations
        );
    }
}
