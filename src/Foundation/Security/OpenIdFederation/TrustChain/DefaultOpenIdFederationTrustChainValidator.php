<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OpenIdFederation\TrustChain;

use Sif\Foundation\Security\Contracts\OpenIdFederationTrustChainValidationPolicyInterface;
use Sif\Foundation\Security\OpenIdFederation\OpenIdFederationStatementValidationContext;

final readonly class DefaultOpenIdFederationTrustChainValidator implements OpenIdFederationTrustChainValidationPolicyInterface
{
    public function validate(
        OpenIdFederationTrustChain $chain,
        OpenIdFederationStatementValidationContext $context
    ): OpenIdFederationTrustChainValidationResult {
        $violations = [];
        $seen = [];

        $leafEntityId = $chain->leafEntityId();
        $seen[$leafEntityId] = true;

        if (
            $context->expectedEntityId() !== null
            && $leafEntityId !== $context->expectedEntityId()
        ) {
            $violations[] = 'unexpected_leaf_entity_identifier';
        }

        $expectedSubject = $leafEntityId;

        foreach ($chain->subordinateStatements() as $statement) {
            $entityStatement = $statement->statement();

            if ($statement->subordinateEntityId() !== $expectedSubject) {
                $violations[] = 'trust_chain_subject_discontinuity';
            }

            if ($entityStatement->issuedAt() > $context->evaluatedAt()) {
                $violations[] = 'trust_chain_statement_issued_in_future';
            }

            if ($entityStatement->expiresAt() <= $context->evaluatedAt()) {
                $violations[] = 'trust_chain_statement_expired';
            }

            $superiorEntityId = $statement->superiorEntityId();

            if (isset($seen[$superiorEntityId])) {
                $violations[] = 'trust_chain_cycle_detected';
            }

            $seen[$superiorEntityId] = true;
            $expectedSubject = $superiorEntityId;
        }

        if ($expectedSubject !== $chain->trustAnchorEntityId()) {
            $violations[] = 'trust_chain_does_not_terminate_at_anchor';
        }

        $anchorStatement = $chain->trustAnchorConfiguration()->statement();

        if ($anchorStatement->issuedAt() > $context->evaluatedAt()) {
            $violations[] = 'trust_anchor_configuration_issued_in_future';
        }

        if ($anchorStatement->expiresAt() <= $context->evaluatedAt()) {
            $violations[] = 'trust_anchor_configuration_expired';
        }

        return new OpenIdFederationTrustChainValidationResult(
            $violations === [],
            array_values(array_unique($violations))
        );
    }
}
