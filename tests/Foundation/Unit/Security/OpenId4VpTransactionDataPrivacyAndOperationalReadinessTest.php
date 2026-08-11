<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OpenId4VpOperationalReadinessEvaluatorInterface;
use Sif\Foundation\Security\Contracts\OpenId4VpPrivacyPolicyInterface;
use Sif\Foundation\Security\Contracts\OpenId4VpTransactionBindingPolicyInterface;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpOperationalReadinessContext;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpOperationalReadinessReport;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpPrivacyContext;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpPrivacyDecision;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpTransactionData;

final class OpenId4VpTransactionDataPrivacyAndOperationalReadinessTest extends TestCase
{
    public function testTransactionDataKeepsIdTypeAndAttributesExplicit(): void
    {
        $data = new OpenId4VpTransactionData(
            'transaction-001',
            'payment',
            ['amount' => '100.00', 'currency' => 'USD']
        );

        self::assertSame('transaction-001', $data->transactionId());
        self::assertSame('payment', $data->type());
        self::assertSame('100.00', $data->attributes()['amount']);
        self::assertSame('USD', $data->attributes()['currency']);
    }

    public function testPrivacyContextKeepsRequestedAllowedAndSensitiveClaimsExplicit(): void
    {
        $context = new OpenId4VpPrivacyContext(
            ['given_name', 'family_name', 'email'],
            ['given_name', 'family_name'],
            ['email']
        );

        self::assertSame(
            ['given_name', 'family_name', 'email'],
            $context->requestedClaims()
        );
        self::assertSame(
            ['given_name', 'family_name'],
            $context->allowedClaims()
        );
        self::assertSame(['email'], $context->sensitiveClaims());
    }

    public function testPrivacyDecisionKeepsDisclosureAndBlockingExplicit(): void
    {
        $decision = new OpenId4VpPrivacyDecision(
            true,
            ['given_name', 'family_name'],
            ['email'],
            ['sensitive claim omitted']
        );

        self::assertTrue($decision->allowed());
        self::assertSame(
            ['given_name', 'family_name'],
            $decision->disclosableClaims()
        );
        self::assertSame(['email'], $decision->blockedClaims());
        self::assertSame(
            ['sensitive claim omitted'],
            $decision->warnings()
        );
    }

    public function testOperationalReadinessKeepsCapabilitiesAndControlsExplicit(): void
    {
        $context = new OpenId4VpOperationalReadinessContext(
            [
                'authorization-request',
                'presentation-query',
                'request-object',
                'response-protection',
                'vp-token-processing',
                'digital-credentials-api',
                'transaction-binding',
                'privacy-policy',
            ],
            [
                'nonce-validation',
                'verifier-authentication',
                'response-destination-policy',
                'presentation-binding',
            ]
        );

        self::assertContains(
            'transaction-binding',
            $context->availableCapabilities()
        );
        self::assertContains(
            'verifier-authentication',
            $context->activeControls()
        );

        $report = new OpenId4VpOperationalReadinessReport(
            false,
            ['verifier_trust_policy_unavailable'],
            ['privacy policy requires review']
        );

        self::assertFalse($report->ready());
        self::assertSame(
            ['verifier_trust_policy_unavailable'],
            $report->blockingIssues()
        );
        self::assertSame(
            ['privacy policy requires review'],
            $report->warnings()
        );
    }

    public function testTransactionPrivacyAndReadinessContractsAreTyped(): void
    {
        $transaction = new \ReflectionMethod(
            OpenId4VpTransactionBindingPolicyInterface::class,
            'validate'
        );
        $privacy = new \ReflectionMethod(
            OpenId4VpPrivacyPolicyInterface::class,
            'decide'
        );
        $readiness = new \ReflectionMethod(
            OpenId4VpOperationalReadinessEvaluatorInterface::class,
            'evaluate'
        );

        self::assertSame(
            'void',
            (string) $transaction->getReturnType()
        );
        self::assertSame(
            OpenId4VpPrivacyDecision::class,
            (string) $privacy->getReturnType()
        );
        self::assertSame(
            OpenId4VpOperationalReadinessReport::class,
            (string) $readiness->getReturnType()
        );
    }

    public function testPrivacyAndReadinessLayerRemainsInfrastructureNeutral(): void
    {
        foreach ([
            OpenId4VpTransactionBindingPolicyInterface::class,
            OpenId4VpPrivacyPolicyInterface::class,
            OpenId4VpOperationalReadinessEvaluatorInterface::class,
            OpenId4VpTransactionData::class,
            OpenId4VpPrivacyContext::class,
            OpenId4VpPrivacyDecision::class,
            OpenId4VpOperationalReadinessContext::class,
            OpenId4VpOperationalReadinessReport::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents((string) $reflection->getFileName());

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Guzzle', $source);
            self::assertStringNotContainsString('Symfony', $source);
            self::assertStringNotContainsString('Laravel', $source);
        }
    }
}
