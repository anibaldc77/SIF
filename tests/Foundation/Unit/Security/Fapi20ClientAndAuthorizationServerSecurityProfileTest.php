<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\FapiAuthorizationServerPolicyInterface;
use Sif\Foundation\Security\Contracts\FapiAuthorizationServerSecurityRequirementsProviderInterface;
use Sif\Foundation\Security\Contracts\FapiClientPolicyInterface;
use Sif\Foundation\Security\Contracts\FapiClientSecurityRequirementsProviderInterface;
use Sif\Foundation\Security\Fapi\FapiAuthorizationServerSecurityAssessment;
use Sif\Foundation\Security\Fapi\FapiAuthorizationServerSecurityRequirements;
use Sif\Foundation\Security\Fapi\FapiClientSecurityAssessment;
use Sif\Foundation\Security\Fapi\FapiClientSecurityRequirements;

final class Fapi20ClientAndAuthorizationServerSecurityProfileTest extends TestCase
{
    public function testClientRequirementsDefaultToHighSecurityProfile(): void
    {
        $r = new FapiClientSecurityRequirements();
        self::assertTrue($r->requireConfidentialClient());
        self::assertTrue($r->requirePkce());
        self::assertTrue($r->requirePar());
        self::assertTrue($r->requireSenderConstrainedTokens());
        self::assertContains('private_key_jwt', $r->allowedTokenEndpointAuthMethods());
    }

    public function testAuthorizationServerRequirementsDefaultToHighSecurityProfile(): void
    {
        $r = new FapiAuthorizationServerSecurityRequirements();
        self::assertTrue($r->requireParEndpoint());
        self::assertTrue($r->requireIssuerMetadata());
        self::assertTrue($r->requireSenderConstrainedTokens());
        self::assertSame(['S256'], $r->supportedCodeChallengeMethods());
    }

    public function testAssessmentsRepresentViolationsExplicitly(): void
    {
        $c = new FapiClientSecurityAssessment(false, ['public_client_not_allowed']);
        $a = new FapiAuthorizationServerSecurityAssessment(false, ['par_endpoint_missing']);

        self::assertFalse($c->compliant());
        self::assertSame(['public_client_not_allowed'], $c->violations());
        self::assertFalse($a->compliant());
        self::assertSame(['par_endpoint_missing'], $a->violations());
    }

    public function testExistingPolicyMethodsRemainBackwardCompatible(): void
    {
        self::assertSame(
            'void',
            (string) (new \ReflectionMethod(FapiClientPolicyInterface::class, 'validate'))->getReturnType()
        );
        self::assertSame(
            'void',
            (string) (new \ReflectionMethod(
                FapiAuthorizationServerPolicyInterface::class,
                'validateConfiguration'
            ))->getReturnType()
        );
    }

    public function testExtendedAssessmentContractsAreTyped(): void
    {
        self::assertSame(
            FapiClientSecurityAssessment::class,
            (string) (new \ReflectionMethod(FapiClientPolicyInterface::class, 'assess'))->getReturnType()
        );
        self::assertSame(
            FapiAuthorizationServerSecurityAssessment::class,
            (string) (new \ReflectionMethod(
                FapiAuthorizationServerPolicyInterface::class,
                'assess'
            ))->getReturnType()
        );
    }

    public function testRequirementsProvidersAreTyped(): void
    {
        self::assertSame(
            FapiClientSecurityRequirements::class,
            (string) (new \ReflectionMethod(
                FapiClientSecurityRequirementsProviderInterface::class,
                'requirements'
            ))->getReturnType()
        );
        self::assertSame(
            FapiAuthorizationServerSecurityRequirements::class,
            (string) (new \ReflectionMethod(
                FapiAuthorizationServerSecurityRequirementsProviderInterface::class,
                'requirements'
            ))->getReturnType()
        );
    }

    public function testFapiSecurityRequirementsRemainInfrastructureNeutral(): void
    {
        foreach ([
            FapiClientPolicyInterface::class,
            FapiAuthorizationServerPolicyInterface::class,
            FapiClientSecurityRequirementsProviderInterface::class,
            FapiAuthorizationServerSecurityRequirementsProviderInterface::class,
            FapiClientSecurityRequirements::class,
            FapiAuthorizationServerSecurityRequirements::class,
            FapiClientSecurityAssessment::class,
            FapiAuthorizationServerSecurityAssessment::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents((string) $reflection->getFileName());

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('openssl_', strtolower($source));
            self::assertStringNotContainsString('Symfony', $source);
            self::assertStringNotContainsString('Laravel', $source);
        }
    }
}
