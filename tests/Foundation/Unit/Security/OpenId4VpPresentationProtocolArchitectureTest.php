<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OpenId4VpAuthorizationRequestValidatorInterface;
use Sif\Foundation\Security\Contracts\OpenId4VpAuthorizationResponseValidatorInterface;
use Sif\Foundation\Security\Contracts\OpenId4VpPresentationQueryValidatorInterface;
use Sif\Foundation\Security\Contracts\OpenId4VpVpTokenResolverInterface;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpAuthorizationRequest;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpAuthorizationResponse;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpPresentationAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpPresentationContext;

final class OpenId4VpPresentationProtocolArchitectureTest extends TestCase
{
    public function testAuthorizationRequestKeepsVerifierNonceAndQueryExplicit(): void
    {
        $request = new OpenId4VpAuthorizationRequest(
            'verifier-client-001',
            'nonce-001',
            'vp_token',
            'direct_post',
            'https://verifier.example.test/response',
            ['credentials' => [['id' => 'identity']]]
        );

        self::assertSame('verifier-client-001', $request->clientId());
        self::assertSame('nonce-001', $request->nonce());
        self::assertSame('vp_token', $request->responseType());
        self::assertSame('direct_post', $request->responseMode());
        self::assertSame(
            'https://verifier.example.test/response',
            $request->responseUri()
        );
        self::assertArrayHasKey('credentials', $request->presentationQuery());
    }

    public function testAuthorizationResponseKeepsVpTokensAndStateExplicit(): void
    {
        $response = new OpenId4VpAuthorizationResponse(
            ['vp-token-001', 'vp-token-002'],
            'state-001'
        );

        self::assertSame(
            ['vp-token-001', 'vp-token-002'],
            $response->vpTokens()
        );
        self::assertSame('state-001', $response->state());
    }

    public function testPresentationContextBindsVerifierNonceOriginsAndTransaction(): void
    {
        $context = new OpenId4VpPresentationContext(
            'verifier-client-001',
            'nonce-001',
            ['https://verifier.example.test'],
            'wallet-nonce-001',
            'transaction-001'
        );

        self::assertSame('verifier-client-001', $context->verifierId());
        self::assertSame('nonce-001', $context->nonce());
        self::assertSame(
            ['https://verifier.example.test'],
            $context->expectedOrigins()
        );
        self::assertSame('wallet-nonce-001', $context->walletNonce());
        self::assertSame('transaction-001', $context->transactionId());
    }

    public function testPresentationAssessmentAggregatesSecurityChecks(): void
    {
        $assessment = new OpenId4VpPresentationAssessment(
            true,
            true,
            true,
            true
        );

        self::assertTrue($assessment->valid());
        self::assertTrue($assessment->nonceValid());
        self::assertTrue($assessment->verifierBound());
        self::assertTrue($assessment->presentationQuerySatisfied());
    }

    public function testProtocolContractsAreTyped(): void
    {
        $request = new \ReflectionMethod(
            OpenId4VpAuthorizationRequestValidatorInterface::class,
            'validate'
        );
        $response = new \ReflectionMethod(
            OpenId4VpAuthorizationResponseValidatorInterface::class,
            'validate'
        );

        self::assertSame(
            OpenId4VpPresentationAssessment::class,
            (string) $request->getReturnType()
        );
        self::assertSame(
            OpenId4VpPresentationAssessment::class,
            (string) $response->getReturnType()
        );

        foreach ([
            OpenId4VpPresentationQueryValidatorInterface::class,
            OpenId4VpVpTokenResolverInterface::class,
        ] as $contract) {
            self::assertTrue(
                (new \ReflectionClass($contract))->isInterface()
            );
        }
    }

    public function testProtocolLayerRemainsCredentialFormatAndTransportNeutral(): void
    {
        foreach ([
            OpenId4VpAuthorizationRequestValidatorInterface::class,
            OpenId4VpAuthorizationResponseValidatorInterface::class,
            OpenId4VpPresentationQueryValidatorInterface::class,
            OpenId4VpVpTokenResolverInterface::class,
            OpenId4VpAuthorizationRequest::class,
            OpenId4VpAuthorizationResponse::class,
            OpenId4VpPresentationContext::class,
            OpenId4VpPresentationAssessment::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Guzzle', $source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('SD-JWT', $source);
            self::assertStringNotContainsString('mdoc', $source);
        }
    }
}
