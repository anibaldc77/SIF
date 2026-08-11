<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OpenId4VpProductReadinessEvaluatorInterface;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpProductReadinessContext;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpProductReadinessReport;

final class OpenId4VpProductCompletionTest extends TestCase
{
    public function testProductReadinessContextRepresentsCompletedProtocolSurface(): void
    {
        $context = new OpenId4VpProductReadinessContext(
            [
                'authorization-request-validation',
                'presentation-query',
                'credential-selection',
                'request-object',
                'request-uri',
                'verifier-authentication',
                'vp-token-processing',
                'presentation-submission',
                'direct-post',
                'response-protection',
                'digital-credentials-api',
                'transaction-binding',
                'privacy-policy',
            ],
            [
                'nonce-validation',
                'presentation-binding',
                'response-destination-policy',
                'protected-response-validation',
                'transaction-binding',
                'privacy-minimization',
            ],
            [
                'operational-readiness',
                'failure-mapping',
                'telemetry-boundary',
            ]
        );

        self::assertCount(13, $context->capabilities());
        self::assertContains('digital-credentials-api', $context->capabilities());
        self::assertContains('privacy-minimization', $context->securityControls());
        self::assertContains('operational-readiness', $context->operationalControls());
    }

    public function testProductReadinessReportRepresentsReleaseGate(): void
    {
        $ready = new OpenId4VpProductReadinessReport(true);

        self::assertTrue($ready->ready());
        self::assertSame([], $ready->blockingIssues());
        self::assertSame([], $ready->warnings());

        $blocked = new OpenId4VpProductReadinessReport(
            false,
            ['verifier_trust_policy_unavailable'],
            ['telemetry integration pending']
        );

        self::assertFalse($blocked->ready());
        self::assertSame(
            ['verifier_trust_policy_unavailable'],
            $blocked->blockingIssues()
        );
        self::assertSame(
            ['telemetry integration pending'],
            $blocked->warnings()
        );
    }

    public function testProductReadinessContractIsTyped(): void
    {
        $method = new \ReflectionMethod(
            OpenId4VpProductReadinessEvaluatorInterface::class,
            'evaluate'
        );

        self::assertSame(1, $method->getNumberOfParameters());
        self::assertSame(
            OpenId4VpProductReadinessContext::class,
            (string) $method->getParameters()[0]->getType()
        );
        self::assertSame(
            OpenId4VpProductReadinessReport::class,
            (string) $method->getReturnType()
        );
    }

    public function testProductCompletionLayerRemainsInfrastructureNeutral(): void
    {
        foreach ([
            OpenId4VpProductReadinessEvaluatorInterface::class,
            OpenId4VpProductReadinessContext::class,
            OpenId4VpProductReadinessReport::class,
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

    public function testCompletionDoesNotCollapseProtocolPoliciesIntoOneService(): void
    {
        self::assertTrue(
            interface_exists(
                \Sif\Foundation\Security\Contracts\OpenId4VpAuthorizationRequestValidatorInterface::class
            )
        );
        self::assertTrue(
            interface_exists(
                \Sif\Foundation\Security\Contracts\OpenId4VpPresentationQueryEvaluatorInterface::class
            )
        );
        self::assertTrue(
            interface_exists(
                \Sif\Foundation\Security\Contracts\OpenId4VpRequestObjectVerifierInterface::class
            )
        );
        self::assertTrue(
            interface_exists(
                \Sif\Foundation\Security\Contracts\OpenId4VpVpTokenProcessorInterface::class
            )
        );
        self::assertTrue(
            interface_exists(
                \Sif\Foundation\Security\Contracts\OpenId4VpResponseTransportInterface::class
            )
        );
        self::assertTrue(
            interface_exists(
                \Sif\Foundation\Security\Contracts\OpenId4VpDigitalCredentialsTransportInterface::class
            )
        );
        self::assertTrue(
            interface_exists(
                \Sif\Foundation\Security\Contracts\OpenId4VpTransactionBindingPolicyInterface::class
            )
        );
        self::assertTrue(
            interface_exists(
                \Sif\Foundation\Security\Contracts\OpenId4VpPrivacyPolicyInterface::class
            )
        );
    }

    public function testWp247CompletionSurfaceIsPresent(): void
    {
        $classes = [
            \Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpAuthorizationRequest::class,
            \Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpPresentationContext::class,
            \Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpTransactionData::class,
            OpenId4VpProductReadinessContext::class,
            OpenId4VpProductReadinessReport::class,
        ];

        foreach ($classes as $class) {
            self::assertTrue(class_exists($class), $class);
        }
    }
}
