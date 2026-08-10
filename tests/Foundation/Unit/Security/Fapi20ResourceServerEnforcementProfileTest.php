<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\FapiProtectedResourceEnforcementInterface;
use Sif\Foundation\Security\Contracts\FapiResourceServerPolicyInterface;
use Sif\Foundation\Security\Contracts\FapiResourceServerSecurityRequirementsProviderInterface;
use Sif\Foundation\Security\Fapi\FapiResourceServerAssessment;
use Sif\Foundation\Security\Fapi\FapiResourceServerRequestContext;
use Sif\Foundation\Security\Fapi\FapiResourceServerSecurityRequirements;

final class Fapi20ResourceServerEnforcementProfileTest extends TestCase
{
    public function testRequirementsDefaultToStrictResourceServerProfile(): void
    {
        $requirements = new FapiResourceServerSecurityRequirements();

        self::assertTrue($requirements->requireSenderConstraint());
        self::assertTrue($requirements->requireExactResourceMatch());
        self::assertTrue($requirements->requireActiveToken());
        self::assertTrue($requirements->rejectBearerDowngrade());
        self::assertTrue($requirements->requireAudienceValidation());
    }

    public function testRequestContextMakesValidatedSecurityStateExplicit(): void
    {
        $context = new FapiResourceServerRequestContext(
            'https://api.example.test',
            'client-001',
            'user-001',
            true,
            true,
            true
        );

        self::assertSame('https://api.example.test', $context->resource());
        self::assertSame('client-001', $context->clientId());
        self::assertSame('user-001', $context->subject());
        self::assertTrue($context->tokenActive());
        self::assertTrue($context->senderConstraintValidated());
        self::assertTrue($context->audienceValidated());
    }

    public function testAssessmentRepresentsViolationsExplicitly(): void
    {
        $assessment = new FapiResourceServerAssessment(
            false,
            ['sender_constraint_missing']
        );

        self::assertFalse($assessment->compliant());
        self::assertSame(
            ['sender_constraint_missing'],
            $assessment->violations()
        );
    }

    public function testExistingResourceServerValidationMethodRemainsCompatible(): void
    {
        $method = new \ReflectionMethod(
            FapiResourceServerPolicyInterface::class,
            'validateConfiguration'
        );

        self::assertSame('void', (string) $method->getReturnType());
    }

    public function testResourceServerAssessmentContractIsTyped(): void
    {
        $method = new \ReflectionMethod(
            FapiResourceServerPolicyInterface::class,
            'assess'
        );

        self::assertSame(
            FapiResourceServerAssessment::class,
            (string) $method->getReturnType()
        );
    }

    public function testRequirementsProviderAndEnforcementAreTyped(): void
    {
        $requirements = new \ReflectionMethod(
            FapiResourceServerSecurityRequirementsProviderInterface::class,
            'requirements'
        );
        $enforce = new \ReflectionMethod(
            FapiProtectedResourceEnforcementInterface::class,
            'enforce'
        );

        self::assertSame(
            FapiResourceServerSecurityRequirements::class,
            (string) $requirements->getReturnType()
        );
        self::assertSame(
            FapiResourceServerAssessment::class,
            (string) $enforce->getReturnType()
        );
    }

    public function testResourceServerProfileRemainsInfrastructureNeutral(): void
    {
        foreach ([
            FapiResourceServerPolicyInterface::class,
            FapiResourceServerSecurityRequirementsProviderInterface::class,
            FapiProtectedResourceEnforcementInterface::class,
            FapiResourceServerSecurityRequirements::class,
            FapiResourceServerRequestContext::class,
            FapiResourceServerAssessment::class,
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
