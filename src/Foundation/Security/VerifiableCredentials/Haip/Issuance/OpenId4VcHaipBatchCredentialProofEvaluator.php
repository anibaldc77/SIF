<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance;

use InvalidArgumentException;
use Sif\Foundation\Security\Contracts\OpenId4VcHaipCredentialProofPolicyInterface;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\BatchCredentialIssuanceRequest;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceContext;

final readonly class OpenId4VcHaipBatchCredentialProofEvaluator
{
    public function __construct(
        private OpenId4VcHaipCredentialProofPolicyInterface $policy
    ) {
    }

    /**
     * Evaluates a batch against the issuer of its issuance operation.
     *
     * @param list<OpenId4VcHaipCredentialProofContext> $contexts
     */
    public function assessForIssuance(
        BatchCredentialIssuanceRequest $request,
        CredentialIssuanceContext $issuanceContext,
        array $contexts
    ): OpenId4VcHaipBatchCredentialProofAssessment {
        if (
            !array_is_list($contexts)
            || count($request->requests()) !== count($contexts)
        ) {
            throw new InvalidArgumentException(
                'HAIP batch requests and proof contexts must correspond.'
            );
        }

        foreach ($contexts as $context) {
            if ($context->issuerIdentifier() !== $issuanceContext->issuer()) {
                throw new InvalidArgumentException(
                    'HAIP batch credential issuer mismatch.'
                );
            }
        }

        return $this->assess($request, $contexts);
    }
    /**
     * @param list<OpenId4VcHaipCredentialProofContext> $contexts
     */
    public function assess(
        BatchCredentialIssuanceRequest $request,
        array $contexts
    ): OpenId4VcHaipBatchCredentialProofAssessment {
        $requests = $request->requests();

        if (
            !array_is_list($contexts)
            || count($requests) !== count($contexts)
        ) {
            throw new InvalidArgumentException(
                'HAIP batch requests and proof contexts must correspond.'
            );
        }

        foreach ($requests as $index => $item) {
            if (
                $item->credentialConfigurationId()
                !== $contexts[$index]->credentialConfigurationId()
            ) {
                throw new InvalidArgumentException(
                    'HAIP batch credential configuration mismatch.'
                );
            }
        }

        $assessments = [];

        foreach ($contexts as $context) {
            $assessments[] = $this->policy->assess($context);
        }

        return new OpenId4VcHaipBatchCredentialProofAssessment(
            $assessments
        );
    }
}