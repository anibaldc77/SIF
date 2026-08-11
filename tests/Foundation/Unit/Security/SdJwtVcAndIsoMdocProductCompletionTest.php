<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\HighAssuranceCredentialProductReadinessEvaluatorInterface;
use Sif\Foundation\Security\VerifiableCredentials\Formats\HighAssuranceCredentialProductCapabilities;
use Sif\Foundation\Security\VerifiableCredentials\Formats\HighAssuranceCredentialProductProfile;
use Sif\Foundation\Security\VerifiableCredentials\Formats\HighAssuranceCredentialProductReadinessReport;

final class SdJwtVcAndIsoMdocProductCompletionTest extends TestCase
{
    public function testCapabilitiesRepresentCompletedWp248Surface(): void
    {
        $capabilities = new HighAssuranceCredentialProductCapabilities();

        self::assertTrue($capabilities->sdJwtVc());
        self::assertTrue($capabilities->isoMdoc());
        self::assertTrue($capabilities->selectiveDisclosure());
        self::assertTrue($capabilities->issuerTrust());
        self::assertTrue($capabilities->statusValidation());
        self::assertTrue($capabilities->holderBinding());
        self::assertTrue($capabilities->msoValidation());
        self::assertTrue($capabilities->deviceAuthentication());
        self::assertTrue($capabilities->openid4VciInteroperability());
        self::assertTrue($capabilities->openid4VpInteroperability());
        self::assertTrue($capabilities->privacyPolicy());
        self::assertTrue($capabilities->operationalReadiness());
        self::assertCount(12, $capabilities->toArray());
    }

    public function testProductProfileKeepsSecurityRequirementsExplicit(): void
    {
        $profile = new HighAssuranceCredentialProductProfile(
            'high-assurance-credentials',
            new HighAssuranceCredentialProductCapabilities()
        );

        self::assertSame(
            'high-assurance-credentials',
            $profile->name()
        );
        self::assertTrue($profile->requireIssuerTrust());
        self::assertTrue($profile->requireStatusValidation());
        self::assertTrue($profile->requireHolderOrDeviceBinding());
        self::assertTrue($profile->requirePrivacyPolicy());
        self::assertTrue($profile->requireOperationalReadiness());
    }

    public function testProductReadinessReportCanRepresentReadyAndBlockedStates(): void
    {
        $ready = new HighAssuranceCredentialProductReadinessReport(true);

        self::assertTrue($ready->ready());
        self::assertSame([], $ready->blockingIssues());
        self::assertSame([], $ready->warnings());

        $blocked = new HighAssuranceCredentialProductReadinessReport(
            false,
            ['trust_store_unavailable'],
            ['profile version requires review']
        );

        self::assertFalse($blocked->ready());
        self::assertSame(
            ['trust_store_unavailable'],
            $blocked->blockingIssues()
        );
        self::assertSame(
            ['profile version requires review'],
            $blocked->warnings()
        );
    }

    public function testProductReadinessContractIsTyped(): void
    {
        $method = new \ReflectionMethod(
            HighAssuranceCredentialProductReadinessEvaluatorInterface::class,
            'evaluate'
        );

        self::assertSame(
            HighAssuranceCredentialProductProfile::class,
            (string) $method->getParameters()[0]->getType()
        );
        self::assertSame(
            HighAssuranceCredentialProductReadinessReport::class,
            (string) $method->getReturnType()
        );
    }

    public function testCompletionPreservesSpecializedContracts(): void
    {
        foreach ([
            \Sif\Foundation\Security\Contracts\SdJwtVcCredentialProcessorInterface::class,
            \Sif\Foundation\Security\Contracts\SdJwtVcIssuerTrustEvaluatorInterface::class,
            \Sif\Foundation\Security\Contracts\IsoMdocCredentialProcessorInterface::class,
            \Sif\Foundation\Security\Contracts\IsoMdocIssuerAuthenticationVerifierInterface::class,
            \Sif\Foundation\Security\Contracts\OpenId4VciCredentialFormatAdapterInterface::class,
            \Sif\Foundation\Security\Contracts\OpenId4VpCredentialFormatAdapterInterface::class,
            \Sif\Foundation\Security\Contracts\HighAssurancePrivacyPolicyInterface::class,
        ] as $contract) {
            self::assertTrue(
                interface_exists($contract),
                $contract
            );
        }
    }

    public function testProductCompletionLayerRemainsInfrastructureAndCryptoNeutral(): void
    {
        foreach ([
            HighAssuranceCredentialProductReadinessEvaluatorInterface::class,
            HighAssuranceCredentialProductCapabilities::class,
            HighAssuranceCredentialProductProfile::class,
            HighAssuranceCredentialProductReadinessReport::class,
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
