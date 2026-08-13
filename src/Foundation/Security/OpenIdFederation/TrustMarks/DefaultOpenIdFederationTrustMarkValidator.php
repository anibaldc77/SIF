<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\OpenIdFederation\TrustMarks;

use Sif\Foundation\Security\Contracts\OpenIdFederationTrustMarkValidationPolicyInterface;

final readonly class DefaultOpenIdFederationTrustMarkValidator implements OpenIdFederationTrustMarkValidationPolicyInterface
{
    public function validate(
        OpenIdFederationTrustMark $trustMark,
        OpenIdFederationTrustMarkValidationContext $context
    ): OpenIdFederationTrustMarkValidationResult {
        $violations = [];

        if ($trustMark->issuedAt() > $context->evaluatedAt()) {
            $violations[] = 'trust_mark_issued_in_future';
        }

        if (
            $trustMark->expiresAt() !== null
            && $trustMark->expiresAt() <= $context->evaluatedAt()
        ) {
            $violations[] = 'trust_mark_expired';
        }

        if (
            $context->expectedSubjectEntityId() !== null
            && $trustMark->subjectEntityId()
            !== $context->expectedSubjectEntityId()
        ) {
            $violations[] = 'unexpected_trust_mark_subject';
        }

        if (
            $context->expectedTrustMarkId() !== null
            && $trustMark->trustMarkId()
            !== $context->expectedTrustMarkId()
        ) {
            $violations[] = 'unexpected_trust_mark_identifier';
        }

        return new OpenIdFederationTrustMarkValidationResult(
            $violations === [],
            $violations
        );
    }
}
