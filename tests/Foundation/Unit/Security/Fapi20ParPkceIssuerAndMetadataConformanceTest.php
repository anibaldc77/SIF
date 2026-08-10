<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\FapiAuthorizationFlowPolicyInterface;
use Sif\Foundation\Security\Contracts\FapiAuthorizationRequestSecurityRequirementsProviderInterface;
use Sif\Foundation\Security\Contracts\FapiMetadataConformancePolicyInterface;
use Sif\Foundation\Security\Contracts\FapiMetadataConformanceRequirementsProviderInterface;
use Sif\Foundation\Security\Fapi\FapiAuthorizationFlowAssessment;
use Sif\Foundation\Security\Fapi\FapiAuthorizationRequestSecurityRequirements;
use Sif\Foundation\Security\Fapi\FapiMetadataConformanceAssessment;
use Sif\Foundation\Security\Fapi\FapiMetadataConformanceRequirements;

final class Fapi20ParPkceIssuerAndMetadataConformanceTest extends TestCase
{
    public function testAuthorizationRequestRequirementsReflectFapiDefaults(): void
    {
        $requirements = new FapiAuthorizationRequestSecurityRequirements();

        self::assertTrue(
            $requirements->requireAuthorizationCodeResponseType()
        );
        self::assertTrue($requirements->requirePar());
        self::assertTrue($requirements->requireClientAuthenticatedPar());
        self::assertTrue($requirements->requirePkceS256());
        self::assertTrue($requirements->requireRedirectUriInPar());
        self::assertTrue(
            $requirements->requireIssuerParameterValidation()
        );
        self::assertSame(
            599,
            $requirements->maximumParLifetimeSeconds()
        );
    }

    public function testMetadataRequirementsReflectFapiDiscoveryRules(): void
    {
        $requirements = new FapiMetadataConformanceRequirements();

        self::assertTrue(
            $requirements->requireAuthoritativeIssuerSource()
        );
        self::assertTrue($requirements->requireExactIssuerMatch());
        self::assertTrue(
            $requirements->requireMetadataDerivedEndpoints()
        );
        self::assertTrue($requirements->requireHttps());
    }

    public function testAuthorizationFlowAssessmentIsExplicit(): void
    {
        $assessment = new FapiAuthorizationFlowAssessment(
            false,
            ['pkce_s256_required']
        );

        self::assertFalse($assessment->compliant());
        self::assertSame(
            ['pkce_s256_required'],
            $assessment->violations()
        );
    }

    public function testMetadataAssessmentIsExplicit(): void
    {
        $assessment = new FapiMetadataConformanceAssessment(
            false,
            ['issuer_mismatch']
        );

        self::assertFalse($assessment->compliant());
        self::assertSame(
            ['issuer_mismatch'],
            $assessment->violations()
        );
    }

    public function testPolicyContractsReturnTypedAssessments(): void
    {
        $authorization = new \ReflectionMethod(
            FapiAuthorizationFlowPolicyInterface::class,
            'assess'
        );
        $metadata = new \ReflectionMethod(
            FapiMetadataConformancePolicyInterface::class,
            'assess'
        );

        self::assertSame(
            FapiAuthorizationFlowAssessment::class,
            (string) $authorization->getReturnType()
        );
        self::assertSame(
            FapiMetadataConformanceAssessment::class,
            (string) $metadata->getReturnType()
        );
    }

    public function testRequirementsProvidersAreTyped(): void
    {
        self::assertSame(
            FapiAuthorizationRequestSecurityRequirements::class,
            (string) (new \ReflectionMethod(
                FapiAuthorizationRequestSecurityRequirementsProviderInterface::class,
                'requirements'
            ))->getReturnType()
        );
        self::assertSame(
            FapiMetadataConformanceRequirements::class,
            (string) (new \ReflectionMethod(
                FapiMetadataConformanceRequirementsProviderInterface::class,
                'requirements'
            ))->getReturnType()
        );
    }

    public function testConformanceLayerRemainsInfrastructureNeutral(): void
    {
        foreach ([
            FapiAuthorizationFlowPolicyInterface::class,
            FapiMetadataConformancePolicyInterface::class,
            FapiAuthorizationRequestSecurityRequirementsProviderInterface::class,
            FapiMetadataConformanceRequirementsProviderInterface::class,
            FapiAuthorizationRequestSecurityRequirements::class,
            FapiMetadataConformanceRequirements::class,
            FapiAuthorizationFlowAssessment::class,
            FapiMetadataConformanceAssessment::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString(
                'http_response_code',
                strtolower($source)
            );
            self::assertStringNotContainsString('Symfony', $source);
            self::assertStringNotContainsString('Laravel', $source);
        }
    }
}
