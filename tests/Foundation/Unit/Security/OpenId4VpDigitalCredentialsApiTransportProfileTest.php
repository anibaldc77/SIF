<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\OpenId4VpDigitalCredentialsPolicyInterface;
use Sif\Foundation\Security\Contracts\OpenId4VpDigitalCredentialsRequestFactoryInterface;
use Sif\Foundation\Security\Contracts\OpenId4VpDigitalCredentialsResponseResolverInterface;
use Sif\Foundation\Security\Contracts\OpenId4VpDigitalCredentialsTransportInterface;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpAuthorizationResponse;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpDigitalCredentialsAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpDigitalCredentialsContext;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpDigitalCredentialsRequest;
use Sif\Foundation\Security\VerifiableCredentials\Presentation\OpenId4Vp\OpenId4VpDigitalCredentialsResponse;

final class OpenId4VpDigitalCredentialsApiTransportProfileTest extends TestCase
{
    public function testDigitalCredentialsRequestKeepsProtocolPayloadAndOriginExplicit(): void
    {
        $request = new OpenId4VpDigitalCredentialsRequest(
            'openid4vp',
            ['request' => 'request-object'],
            'https://verifier.example.test'
        );

        self::assertSame('openid4vp', $request->protocol());
        self::assertSame(
            'request-object',
            $request->payload()['request']
        );
        self::assertSame(
            'https://verifier.example.test',
            $request->origin()
        );
    }

    public function testDigitalCredentialsResponseKeepsProtocolAndPayloadExplicit(): void
    {
        $response = new OpenId4VpDigitalCredentialsResponse(
            'openid4vp',
            ['vp_token' => 'vp-token-001']
        );

        self::assertSame('openid4vp', $response->protocol());
        self::assertSame(
            'vp-token-001',
            $response->payload()['vp_token']
        );
    }

    public function testContextKeepsOriginProtocolsAndUserMediationExplicit(): void
    {
        $context = new OpenId4VpDigitalCredentialsContext(
            'https://verifier.example.test',
            ['openid4vp'],
            true
        );

        self::assertSame(
            'https://verifier.example.test',
            $context->origin()
        );
        self::assertSame(
            ['openid4vp'],
            $context->supportedProtocols()
        );
        self::assertTrue($context->userMediationRequired());
    }

    public function testAssessmentAggregatesProtocolOriginAndMediationChecks(): void
    {
        $assessment = new OpenId4VpDigitalCredentialsAssessment(
            true,
            true,
            true,
            true
        );

        self::assertTrue($assessment->valid());
        self::assertTrue($assessment->protocolSupported());
        self::assertTrue($assessment->originValid());
        self::assertTrue($assessment->userMediationSatisfied());
    }

    public function testDigitalCredentialsContractsAreTyped(): void
    {
        $factory = new \ReflectionMethod(
            OpenId4VpDigitalCredentialsRequestFactoryInterface::class,
            'create'
        );
        $resolver = new \ReflectionMethod(
            OpenId4VpDigitalCredentialsResponseResolverInterface::class,
            'resolve'
        );
        $policy = new \ReflectionMethod(
            OpenId4VpDigitalCredentialsPolicyInterface::class,
            'assess'
        );
        $transport = new \ReflectionMethod(
            OpenId4VpDigitalCredentialsTransportInterface::class,
            'exchange'
        );

        self::assertSame(
            OpenId4VpDigitalCredentialsRequest::class,
            (string) $factory->getReturnType()
        );
        self::assertSame(
            OpenId4VpAuthorizationResponse::class,
            (string) $resolver->getReturnType()
        );
        self::assertSame(
            OpenId4VpDigitalCredentialsAssessment::class,
            (string) $policy->getReturnType()
        );
        self::assertSame(
            OpenId4VpDigitalCredentialsResponse::class,
            (string) $transport->getReturnType()
        );
    }

    public function testDigitalCredentialsProfileRemainsBrowserApiAndFrameworkNeutral(): void
    {
        foreach ([
            OpenId4VpDigitalCredentialsRequestFactoryInterface::class,
            OpenId4VpDigitalCredentialsResponseResolverInterface::class,
            OpenId4VpDigitalCredentialsPolicyInterface::class,
            OpenId4VpDigitalCredentialsTransportInterface::class,
            OpenId4VpDigitalCredentialsRequest::class,
            OpenId4VpDigitalCredentialsResponse::class,
            OpenId4VpDigitalCredentialsContext::class,
            OpenId4VpDigitalCredentialsAssessment::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents((string) $reflection->getFileName());

            self::assertIsString($source);
            self::assertStringNotContainsString('navigator.credentials', strtolower($source));
            self::assertStringNotContainsString('DOM', $source);
            self::assertStringNotContainsString('HTML', $source);
            self::assertStringNotContainsString('Symfony', $source);
            self::assertStringNotContainsString('Laravel', $source);
            self::assertStringNotContainsString('Guzzle', $source);
        }
    }
}
