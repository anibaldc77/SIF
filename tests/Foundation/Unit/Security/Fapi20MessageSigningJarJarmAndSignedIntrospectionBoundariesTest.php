<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\FapiJarmVerifierInterface;
use Sif\Foundation\Security\Contracts\FapiMessageSigningPolicyInterface;
use Sif\Foundation\Security\Contracts\FapiMessageSigningRequirementsProviderInterface;
use Sif\Foundation\Security\Contracts\FapiSignedIntrospectionVerifierInterface;
use Sif\Foundation\Security\Fapi\FapiMessageSigningAssessment;
use Sif\Foundation\Security\Fapi\FapiMessageSigningRequirements;
use Sif\Foundation\Security\Fapi\FapiSignedAuthorizationResponse;
use Sif\Foundation\Security\Fapi\FapiSignedIntrospectionResponse;

final class Fapi20MessageSigningJarJarmAndSignedIntrospectionBoundariesTest extends TestCase
{
    public function testMessageSigningRequirementsCanRequireAllBoundaries(): void
    {
        $requirements = new FapiMessageSigningRequirements(
            true,
            true,
            true,
            ['PS256'],
            ['PS256'],
            ['PS256']
        );

        self::assertTrue($requirements->requireJar());
        self::assertTrue($requirements->requireJarm());
        self::assertTrue($requirements->requireSignedIntrospection());
        self::assertSame(
            ['PS256'],
            $requirements->allowedRequestObjectAlgorithms()
        );
        self::assertSame(
            ['PS256'],
            $requirements->allowedAuthorizationResponseAlgorithms()
        );
        self::assertSame(
            ['PS256'],
            $requirements->allowedIntrospectionResponseAlgorithms()
        );
    }

    public function testSignedAuthorizationResponseKeepsSecurityClaimsExplicit(): void
    {
        $response = new FapiSignedAuthorizationResponse(
            'header.payload.signature',
            'https://issuer.example.test',
            'client-001',
            'code-001',
            'state-001'
        );

        self::assertSame(
            'https://issuer.example.test',
            $response->issuer()
        );
        self::assertSame('client-001', $response->audience());
        self::assertSame('code-001', $response->authorizationCode());
        self::assertSame('state-001', $response->state());
    }

    public function testSignedIntrospectionResponseKeepsSecurityClaimsExplicit(): void
    {
        $response = new FapiSignedIntrospectionResponse(
            'header.payload.signature',
            true,
            'https://issuer.example.test',
            'https://api.example.test',
            ['scope' => 'orders.read']
        );

        self::assertTrue($response->active());
        self::assertSame(
            'https://issuer.example.test',
            $response->issuer()
        );
        self::assertSame(
            'https://api.example.test',
            $response->audience()
        );
        self::assertSame(
            'orders.read',
            $response->claims()['scope']
        );
    }

    public function testAssessmentRepresentsViolationsExplicitly(): void
    {
        $assessment = new FapiMessageSigningAssessment(
            false,
            ['jarm_required']
        );

        self::assertFalse($assessment->compliant());
        self::assertSame(['jarm_required'], $assessment->violations());
    }

    public function testMessageSigningPolicyReturnsTypedAssessment(): void
    {
        $method = new \ReflectionMethod(
            FapiMessageSigningPolicyInterface::class,
            'assess'
        );

        self::assertSame(
            FapiMessageSigningAssessment::class,
            (string) $method->getReturnType()
        );
    }

    public function testVerifierContractsReturnTypedModels(): void
    {
        $jarm = new \ReflectionMethod(
            FapiJarmVerifierInterface::class,
            'verify'
        );
        $introspection = new \ReflectionMethod(
            FapiSignedIntrospectionVerifierInterface::class,
            'verify'
        );

        self::assertSame(
            FapiSignedAuthorizationResponse::class,
            (string) $jarm->getReturnType()
        );
        self::assertSame(
            FapiSignedIntrospectionResponse::class,
            (string) $introspection->getReturnType()
        );
    }

    public function testRequirementsProviderIsTyped(): void
    {
        $method = new \ReflectionMethod(
            FapiMessageSigningRequirementsProviderInterface::class,
            'requirements'
        );

        self::assertSame(
            FapiMessageSigningRequirements::class,
            (string) $method->getReturnType()
        );
    }

    public function testMessageSigningLayerRemainsCryptoAndInfrastructureNeutral(): void
    {
        foreach ([
            FapiMessageSigningPolicyInterface::class,
            FapiMessageSigningRequirementsProviderInterface::class,
            FapiJarmVerifierInterface::class,
            FapiSignedIntrospectionVerifierInterface::class,
            FapiMessageSigningRequirements::class,
            FapiSignedAuthorizationResponse::class,
            FapiSignedIntrospectionResponse::class,
            FapiMessageSigningAssessment::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('openssl_', strtolower($source));
            self::assertStringNotContainsString('firebase', strtolower($source));
            self::assertStringNotContainsString('lcobucci', strtolower($source));
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
        }
    }
}
