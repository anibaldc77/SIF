<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\HighAssuranceCredentialProfilePolicyInterface;
use Sif\Foundation\Security\Contracts\HighAssuranceOperationalReadinessEvaluatorInterface;
use Sif\Foundation\Security\Contracts\HighAssurancePrivacyPolicyInterface;
use Sif\Foundation\Security\VerifiableCredentials\Formats\HighAssuranceCredentialFormat;
use Sif\Foundation\Security\VerifiableCredentials\Formats\HighAssuranceCredentialProfile;
use Sif\Foundation\Security\VerifiableCredentials\Formats\HighAssuranceOperationalReadinessContext;
use Sif\Foundation\Security\VerifiableCredentials\Formats\HighAssuranceOperationalReadinessReport;
use Sif\Foundation\Security\VerifiableCredentials\Formats\HighAssurancePrivacyContext;
use Sif\Foundation\Security\VerifiableCredentials\Formats\HighAssurancePrivacyDecision;

final class HighAssuranceCredentialProfilePrivacyAndOperationalReadinessTest extends TestCase
{
    public function testProfileKeepsFormatsControlsClaimsAndBindingExplicit(): void
    {
        $profile = new HighAssuranceCredentialProfile(
            'high-assurance-identity',
            [
                HighAssuranceCredentialFormat::SdJwtVc,
                HighAssuranceCredentialFormat::IsoMdoc,
            ],
            [
                'issuer-trust',
                'status-validation',
                'holder-or-device-binding',
            ],
            ['given_name', 'family_name'],
            true,
            true
        );

        self::assertSame('high-assurance-identity', $profile->name());
        self::assertCount(2, $profile->allowedFormats());
        self::assertContains(
            'issuer-trust',
            $profile->requiredControls()
        );
        self::assertSame(
            ['given_name', 'family_name'],
            $profile->requiredClaims()
        );
        self::assertTrue($profile->holderBindingRequired());
        self::assertTrue($profile->statusValidationRequired());
    }

    public function testPrivacyContextKeepsRequestedPermittedAndSensitiveClaimsExplicit(): void
    {
        $context = new HighAssurancePrivacyContext(
            ['given_name', 'family_name', 'birth_date'],
            ['given_name', 'family_name'],
            ['birth_date']
        );

        self::assertSame(
            ['given_name', 'family_name', 'birth_date'],
            $context->requestedClaims()
        );
        self::assertSame(
            ['given_name', 'family_name'],
            $context->permittedClaims()
        );
        self::assertSame(
            ['birth_date'],
            $context->sensitiveClaims()
        );
    }

    public function testPrivacyDecisionKeepsDisclosureAndBlockingExplicit(): void
    {
        $decision = new HighAssurancePrivacyDecision(
            true,
            ['given_name', 'family_name'],
            ['birth_date'],
            ['sensitive claim withheld']
        );

        self::assertTrue($decision->allowed());
        self::assertSame(
            ['given_name', 'family_name'],
            $decision->disclosableClaims()
        );
        self::assertSame(
            ['birth_date'],
            $decision->blockedClaims()
        );
        self::assertSame(
            ['sensitive claim withheld'],
            $decision->warnings()
        );
    }

    public function testOperationalReadinessKeepsCapabilitiesAndControlsExplicit(): void
    {
        $context = new HighAssuranceOperationalReadinessContext(
            [
                'sd-jwt-vc',
                'iso-mdoc',
                'openid4vci-format-adapter',
                'openid4vp-format-adapter',
                'privacy-policy',
            ],
            [
                'issuer-trust',
                'status-validation',
                'selective-disclosure',
                'mso-validation',
                'device-authentication',
            ]
        );

        self::assertContains(
            'iso-mdoc',
            $context->availableCapabilities()
        );
        self::assertContains(
            'mso-validation',
            $context->activeControls()
        );

        $report = new HighAssuranceOperationalReadinessReport(
            false,
            ['trust_store_unavailable'],
            ['status freshness policy requires review']
        );

        self::assertFalse($report->ready());
        self::assertSame(
            ['trust_store_unavailable'],
            $report->blockingIssues()
        );
        self::assertSame(
            ['status freshness policy requires review'],
            $report->warnings()
        );
    }

    public function testProfilePrivacyAndReadinessContractsAreTyped(): void
    {
        $profile = new \ReflectionMethod(
            HighAssuranceCredentialProfilePolicyInterface::class,
            'validate'
        );
        $privacy = new \ReflectionMethod(
            HighAssurancePrivacyPolicyInterface::class,
            'decide'
        );
        $readiness = new \ReflectionMethod(
            HighAssuranceOperationalReadinessEvaluatorInterface::class,
            'evaluate'
        );

        self::assertSame(
            'void',
            (string) $profile->getReturnType()
        );
        self::assertSame(
            HighAssurancePrivacyDecision::class,
            (string) $privacy->getReturnType()
        );
        self::assertSame(
            HighAssuranceOperationalReadinessReport::class,
            (string) $readiness->getReturnType()
        );
    }

    public function testHighAssuranceLayerRemainsInfrastructureAndCryptoNeutral(): void
    {
        foreach ([
            HighAssuranceCredentialProfilePolicyInterface::class,
            HighAssurancePrivacyPolicyInterface::class,
            HighAssuranceOperationalReadinessEvaluatorInterface::class,
            HighAssuranceCredentialProfile::class,
            HighAssurancePrivacyContext::class,
            HighAssurancePrivacyDecision::class,
            HighAssuranceOperationalReadinessContext::class,
            HighAssuranceOperationalReadinessReport::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents((string) $reflection->getFileName());

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Guzzle', $source);
            self::assertStringNotContainsString('openssl_', strtolower($source));
            self::assertStringNotContainsString('Firebase', $source);
        }
    }
}
