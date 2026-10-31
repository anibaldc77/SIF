<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Haip\Wallet;

use Sif\Foundation\Security\Contracts\OpenId4VcHaipWalletAuthenticationPolicyInterface;

final readonly class DefaultOpenId4VcHaipWalletAuthenticationPolicy implements OpenId4VcHaipWalletAuthenticationPolicyInterface
{
    public function assess(
        OpenId4VcHaipWalletAuthenticationContext $context
    ): OpenId4VcHaipWalletAuthenticationAssessment {
        $violations = [];
        $attestation = $context->attestation();

        if ($attestation->issuedAt() > $context->evaluatedAt()) {
            $violations[] = 'wallet_attestation_issued_in_future';
        }

        if ($attestation->expiresAt() <= $context->evaluatedAt()) {
            $violations[] = 'wallet_attestation_expired';
        }

        if ($attestation->audience() !== $context->issuerEntityId()) {
            $violations[] = 'wallet_attestation_wrong_audience';
        }

        if (
            $context->clientId() !== null
            && $context->clientId() !== $attestation->subject()
        ) {
            $violations[] = 'wallet_attestation_client_id_subject_mismatch';
        }

        if (!$context->usedAtClientAuthenticationEndpoint()) {
            $violations[] = 'wallet_attestation_client_authentication_required';
        }

        return new OpenId4VcHaipWalletAuthenticationAssessment(
            $violations === [],
            $violations
        );
    }
}
