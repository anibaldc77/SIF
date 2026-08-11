<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OpenId4VpAuthorizationResponseProtectorInterface;
use Sif\Foundation\Security\Contracts\OpenId4VpProtectedAuthorizationResponseValidatorInterface;
use Sif\Foundation\Security\Contracts\OpenId4VpResponseDestinationPolicyInterface;
use Sif\Foundation\Security\Contracts\OpenId4VpResponseTransportInterface;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpProtectedAuthorizationResponse;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpResponseDestination;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpResponseMode;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpResponseProtectionAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpResponseProtectionContext;

final class OpenId4VpResponseModesDirectPostAndResponseProtectionTest extends TestCase
{
    public function testResponseModesAreExplicit(): void
    {
        self::assertSame(
            'query',
            OpenId4VpResponseMode::Query->value
        );
        self::assertSame(
            'fragment',
            OpenId4VpResponseMode::Fragment->value
        );
        self::assertSame(
            'direct_post',
            OpenId4VpResponseMode::DirectPost->value
        );
        self::assertSame(
            'direct_post.jwt',
            OpenId4VpResponseMode::DirectPostJwt->value
        );
    }

    public function testResponseDestinationKeepsUriAndModeExplicit(): void
    {
        $destination = new OpenId4VpResponseDestination(
            'https://verifier.example.test/response',
            OpenId4VpResponseMode::DirectPostJwt
        );

        self::assertSame(
            'https://verifier.example.test/response',
            $destination->uri()
        );
        self::assertSame(
            OpenId4VpResponseMode::DirectPostJwt,
            $destination->mode()
        );
    }

    public function testProtectedResponseKeepsSerializedPayloadAndHeadersExplicit(): void
    {
        $response = new OpenId4VpProtectedAuthorizationResponse(
            'protected-response',
            ['alg' => 'ES256', 'kid' => 'wallet-key-001']
        );

        self::assertSame(
            'protected-response',
            $response->serialized()
        );
        self::assertSame('ES256', $response->headers()['alg']);
        self::assertSame('wallet-key-001', $response->headers()['kid']);
    }

    public function testProtectionContextKeepsVerifierNonceStateAndTransactionExplicit(): void
    {
        $context = new OpenId4VpResponseProtectionContext(
            'verifier-client-001',
            'nonce-001',
            'state-001',
            'transaction-001'
        );

        self::assertSame(
            'verifier-client-001',
            $context->verifierId()
        );
        self::assertSame('nonce-001', $context->nonce());
        self::assertSame('state-001', $context->state());
        self::assertSame(
            'transaction-001',
            $context->transactionId()
        );
    }

    public function testProtectionAssessmentAggregatesBindingChecks(): void
    {
        $assessment = new OpenId4VpResponseProtectionAssessment(
            true,
            true,
            true,
            true,
            true
        );

        self::assertTrue($assessment->valid());
        self::assertTrue($assessment->verifierBound());
        self::assertTrue($assessment->nonceValid());
        self::assertTrue($assessment->stateValid());
        self::assertTrue($assessment->transactionBound());
    }

    public function testResponseProtectionContractsAreTyped(): void
    {
        $protect = new \ReflectionMethod(
            OpenId4VpAuthorizationResponseProtectorInterface::class,
            'protect'
        );
        $validate = new \ReflectionMethod(
            OpenId4VpProtectedAuthorizationResponseValidatorInterface::class,
            'validate'
        );

        self::assertSame(
            OpenId4VpProtectedAuthorizationResponse::class,
            (string) $protect->getReturnType()
        );
        self::assertSame(
            OpenId4VpResponseProtectionAssessment::class,
            (string) $validate->getReturnType()
        );

        foreach ([
            OpenId4VpResponseTransportInterface::class,
            OpenId4VpResponseDestinationPolicyInterface::class,
        ] as $contract) {
            self::assertTrue(
                (new \ReflectionClass($contract))->isInterface()
            );
        }
    }

    public function testResponseLayerRemainsHttpJoseAndFrameworkNeutral(): void
    {
        foreach ([
            OpenId4VpAuthorizationResponseProtectorInterface::class,
            OpenId4VpProtectedAuthorizationResponseValidatorInterface::class,
            OpenId4VpResponseTransportInterface::class,
            OpenId4VpResponseDestinationPolicyInterface::class,
            OpenId4VpResponseDestination::class,
            OpenId4VpProtectedAuthorizationResponse::class,
            OpenId4VpResponseProtectionContext::class,
            OpenId4VpResponseProtectionAssessment::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Guzzle', $source);
            self::assertStringNotContainsString('Symfony', $source);
            self::assertStringNotContainsString('Laravel', $source);
            self::assertStringNotContainsString('Firebase', $source);
            self::assertStringNotContainsString('openssl_', strtolower($source));
        }
    }
}
