<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\IdentityAssuranceEvidenceValidatorInterface;
use Sif\Foundation\Security\Contracts\IdentityAssurancePolicyInterface;
use Sif\Foundation\Security\Contracts\IdentityAssuranceProfileProviderInterface;
use Sif\Foundation\Security\VerifiableCredentials\IdentityAssuranceAssessment;
use Sif\Foundation\Security\VerifiableCredentials\IdentityAssuranceContext;
use Sif\Foundation\Security\VerifiableCredentials\IdentityAssuranceEvidence;
use Sif\Foundation\Security\VerifiableCredentials\IdentityAssuranceLevel;
use Sif\Foundation\Security\VerifiableCredentials\IdentityAssuranceProfile;
use Sif\Foundation\Security\VerifiableCredentials\VerifiedIdentityClaims;

final class IdentityAssuranceVerifiedClaimsAndEvidenceTest extends TestCase
{
    public function testAssuranceLevelIsOpaqueAndExtensible(): void
    {
        $level = new IdentityAssuranceLevel('high');

        self::assertSame('high', $level->value());
    }

    public function testAssuranceContextKeepsLevelEvidenceAndMethodsExplicit(): void
    {
        $context = new IdentityAssuranceContext(
            new IdentityAssuranceLevel('high'),
            ['identity-document', 'address'],
            ['document-verification', 'registry-check'],
            true
        );

        self::assertSame('high', $context->requiredLevel()->value());
        self::assertSame(
            ['identity-document', 'address'],
            $context->requiredEvidenceTypes()
        );
        self::assertSame(
            ['document-verification', 'registry-check'],
            $context->requiredMethods()
        );
        self::assertTrue($context->requireVerifiedClaims());
    }

    public function testAssuranceAssessmentSeparatesMissingEvidenceAndViolations(): void
    {
        $assessment = new IdentityAssuranceAssessment(
            false,
            ['address'],
            ['verification_method_not_allowed'],
            ['document verification is nearing freshness limit']
        );

        self::assertFalse($assessment->satisfied());
        self::assertSame(['address'], $assessment->missingEvidence());
        self::assertSame(
            ['verification_method_not_allowed'],
            $assessment->violations()
        );
        self::assertSame(
            ['document verification is nearing freshness limit'],
            $assessment->warnings()
        );
    }

    public function testAssuranceProfileKeepsAcceptedEvidenceAndMethodsExplicit(): void
    {
        $profile = new IdentityAssuranceProfile(
            'government-high',
            new IdentityAssuranceLevel('high'),
            ['identity-document'],
            ['registry-check']
        );

        self::assertSame('government-high', $profile->name());
        self::assertSame('high', $profile->level()->value());
        self::assertSame(
            ['identity-document'],
            $profile->acceptedEvidenceTypes()
        );
        self::assertSame(
            ['registry-check'],
            $profile->acceptedMethods()
        );
    }

    public function testVerifiedIdentityClaimsCanCarryMultipleEvidenceItems(): void
    {
        $claims = new VerifiedIdentityClaims(
            ['given_name' => 'Alice'],
            [
                new IdentityAssuranceEvidence(
                    'identity-document',
                    'document-verification'
                ),
                new IdentityAssuranceEvidence(
                    'address',
                    'registry-check'
                ),
            ]
        );

        self::assertSame('Alice', $claims->claims()['given_name']);
        self::assertCount(2, $claims->evidence());
    }

    public function testAssuranceContractsAreTyped(): void
    {
        $policy = new \ReflectionMethod(
            IdentityAssurancePolicyInterface::class,
            'assess'
        );
        $provider = new \ReflectionMethod(
            IdentityAssuranceProfileProviderInterface::class,
            'profile'
        );

        self::assertSame(
            IdentityAssuranceAssessment::class,
            (string) $policy->getReturnType()
        );
        self::assertSame(
            IdentityAssuranceProfile::class,
            (string) $provider->getReturnType()
        );

        self::assertTrue(
            (new \ReflectionClass(
                IdentityAssuranceEvidenceValidatorInterface::class
            ))->isInterface()
        );
    }

    public function testIdentityAssuranceLayerRemainsProviderAndStorageNeutral(): void
    {
        foreach ([
            IdentityAssurancePolicyInterface::class,
            IdentityAssuranceProfileProviderInterface::class,
            IdentityAssuranceEvidenceValidatorInterface::class,
            IdentityAssuranceContext::class,
            IdentityAssuranceAssessment::class,
            IdentityAssuranceProfile::class,
            IdentityAssuranceLevel::class,
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
            self::assertStringNotContainsString('KYC', $source);
        }
    }
}
