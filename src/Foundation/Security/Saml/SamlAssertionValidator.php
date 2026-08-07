<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

final readonly class SamlAssertionValidator
{
    public function validate(
        SamlAssertion $assertion,
        SamlAssertionValidationContext $context
    ): SamlAssertionValidationResult {
        $violations = [];

        if (
            $assertion->issuer()->value()
            !== $context->expectedIssuer()->value()
        ) {
            $violations[] = 'issuer_mismatch';
        }

        $conditions = $assertion->conditions();
        $now = $context->now();
        $earliest = $now->sub($context->clockSkew());
        $latest = $now->add($context->clockSkew());

        if (
            $conditions->notBefore() !== null
            && $conditions->notBefore() > $latest
        ) {
            $violations[] = 'conditions_not_yet_valid';
        }

        if (
            $conditions->notOnOrAfter() !== null
            && $conditions->notOnOrAfter() <= $earliest
        ) {
            $violations[] = 'conditions_expired';
        }

        $audienceMatched = false;

        foreach ($conditions->audiences() as $audience) {
            if (
                $audience->value()
                === $context->expectedAudience()->value()
            ) {
                $audienceMatched = true;
                break;
            }
        }

        if (!$audienceMatched) {
            $violations[] = 'audience_mismatch';
        }

        $subjectConfirmation = $assertion->subjectConfirmationData();

        if ($subjectConfirmation === null) {
            $violations[] = 'subject_confirmation_missing';

            return new SamlAssertionValidationResult($violations);
        }

        if (
            $subjectConfirmation->recipient() === null
            || $subjectConfirmation->recipient()
                !== $context->expectedRecipient()
        ) {
            $violations[] = 'subject_recipient_mismatch';
        }

        $expectedInResponseTo = $context->expectedInResponseTo();

        if (
            $expectedInResponseTo !== null
            && (
                $subjectConfirmation->inResponseTo() === null
                || $subjectConfirmation->inResponseTo()->value()
                    !== $expectedInResponseTo->value()
            )
        ) {
            $violations[] = 'subject_in_response_to_mismatch';
        }

        if (
            $subjectConfirmation->notOnOrAfter() === null
            || $subjectConfirmation->notOnOrAfter() <= $earliest
        ) {
            $violations[] = 'subject_confirmation_expired';
        }

        return new SamlAssertionValidationResult($violations);
    }
}
