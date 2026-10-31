<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance;

use Sif\Foundation\Security\Contracts\OpenId4VcHaipAuthorizationConformancePolicyInterface;

final readonly class DefaultOpenId4VcHaipAuthorizationConformancePolicy implements OpenId4VcHaipAuthorizationConformancePolicyInterface
{
    public function assess(
        OpenId4VcHaipAuthorizationContext $context
    ): OpenId4VcHaipAuthorizationAssessment {
        $violations = [];

        if (!$context->authorizationCodeSupported()) {
            $violations[] = 'authorization_code_required';
        }

        if ($context->pkceCodeChallengeMethod() !== 'S256') {
            $violations[] = 'pkce_s256_required';
        }

        if (
            $context->authorizationEndpointUsed()
            && !$context->parUsed()
        ) {
            $violations[] = 'par_required_for_authorization_endpoint';
        }

        if (!$context->authorizationResponseIssuerValidated()) {
            $violations[] = 'authorization_response_issuer_required';
        }

        if (!$context->dpopSupported()) {
            $violations[] = 'dpop_required';
        }

        if (!$context->dpopNonceSupported()) {
            $violations[] = 'dpop_nonce_support_required';
        }

        if (!$context->scopeMapsToCredentialType()) {
            $violations[] = 'scope_credential_type_binding_required';
        }

        return new OpenId4VcHaipAuthorizationAssessment(
            $violations === [],
            $violations
        );
    }
}
