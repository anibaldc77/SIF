<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Saml;

final readonly class SamlSignaturePolicyValidator
{
    public function __construct(
        private SamlSignedDocumentPolicy $policy,
        private SamlSignatureTrustValidator $trustValidator
    ) {
    }

    public function validate(
        SamlSignatureValidationContext $context
    ): SamlXmlSignatureVerificationResult {
        $violations = [];

        if ($this->policy->requireSignedResponse()) {
            $responseResult = $this->trustValidator->validate(
                $context->issuer(),
                $context->responseXml(),
                $context->fingerprint()
            );

            if (!$responseResult->verified()) {
                $violations = array_merge(
                    $violations,
                    $responseResult->violations()
                );
            }
        }

        if ($this->policy->requireSignedAssertion()) {
            $assertionXml = $context->assertionXml();

            if ($assertionXml === null || $assertionXml === '') {
                $violations[] = 'signed_assertion_required';
            } else {
                $assertionResult = $this->trustValidator->validate(
                    $context->issuer(),
                    $assertionXml,
                    $context->fingerprint()
                );

                if (!$assertionResult->verified()) {
                    $violations = array_merge(
                        $violations,
                        $assertionResult->violations()
                    );
                }
            }
        }

        return $violations === []
            ? SamlXmlSignatureVerificationResult::success()
            : SamlXmlSignatureVerificationResult::failed(
                array_values(array_unique($violations))
            );
    }
}
