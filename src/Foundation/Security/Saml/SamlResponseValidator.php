<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

final readonly class SamlResponseValidator
{
    public function validate(
        SamlResponse $response,
        SamlResponseValidationContext $context
    ): SamlResponseValidationResult {
        $violations = [];

        if (!$response->statusCode()->successful()) {
            $violations[] = 'status_not_success';
        }

        if (
            $response->issuer()->value()
            !== $context->expectedIssuer()->value()
        ) {
            $violations[] = 'issuer_mismatch';
        }

        if (
            $response->destination() === null
            || $response->destination()
                !== $context->expectedDestination()
        ) {
            $violations[] = 'destination_mismatch';
        }

        $expectedInResponseTo = $context->expectedInResponseTo();

        if ($expectedInResponseTo !== null) {
            if (
                $response->inResponseTo() === null
                || $response->inResponseTo()->value()
                    !== $expectedInResponseTo->value()
            ) {
                $violations[] = 'in_response_to_mismatch';
            }
        }

        return $violations === []
            ? SamlResponseValidationResult::success()
            : SamlResponseValidationResult::failed($violations);
    }
}
