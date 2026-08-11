<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\HighAssuranceCredentialFormatPolicyInterface;
use Sif\Foundation\Security\Contracts\IsoMdocCredentialProcessorInterface;
use Sif\Foundation\Security\Contracts\SdJwtVcCredentialProcessorInterface;
use Sif\Foundation\Security\VerifiableCredentials\Formats\CredentialFormatProcessingAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Formats\CredentialFormatProcessingContext;
use Sif\Foundation\Security\VerifiableCredentials\Formats\CredentialFormatProfile;
use Sif\Foundation\Security\VerifiableCredentials\Formats\HighAssuranceCredentialFormat;

final class SdJwtVcAndIsoMdocCredentialFormatArchitectureTest extends TestCase
{
    public function testSupportedHighAssuranceFormatsAreExplicit(): void
    {
        self::assertSame(
            'dc+sd-jwt',
            HighAssuranceCredentialFormat::SdJwtVc->value
        );
        self::assertSame(
            'mso_mdoc',
            HighAssuranceCredentialFormat::IsoMdoc->value
        );
    }

    public function testCredentialFormatProfileKeepsVersionAlgorithmsAndOptionsExplicit(): void
    {
        $profile = new CredentialFormatProfile(
            HighAssuranceCredentialFormat::SdJwtVc,
            'draft-ietf-oauth-sd-jwt-vc-17',
            ['ES256'],
            ['key_binding_required' => true]
        );

        self::assertSame(
            HighAssuranceCredentialFormat::SdJwtVc,
            $profile->format()
        );
        self::assertSame(
            'draft-ietf-oauth-sd-jwt-vc-17',
            $profile->profileVersion()
        );
        self::assertSame(['ES256'], $profile->acceptedAlgorithms());
        self::assertTrue($profile->options()['key_binding_required']);
    }

    public function testProcessingContextKeepsTrustClaimsAndBindingExplicit(): void
    {
        $context = new CredentialFormatProcessingContext(
            new DateTimeImmutable('2026-08-11T12:00:00Z'),
            ['https://issuer.example.test'],
            ['given_name', 'family_name'],
            true,
            true
        );

        self::assertSame(
            ['https://issuer.example.test'],
            $context->trustedIssuerIdentifiers()
        );
        self::assertSame(
            ['given_name', 'family_name'],
            $context->requiredClaims()
        );
        self::assertTrue($context->holderBindingRequired());
        self::assertTrue($context->statusValidationRequired());
    }

    public function testProcessingAssessmentAggregatesSecurityChecks(): void
    {
        $assessment = new CredentialFormatProcessingAssessment(
            true,
            true,
            true,
            true,
            true
        );

        self::assertTrue($assessment->valid());
        self::assertTrue($assessment->issuerTrusted());
        self::assertTrue($assessment->signatureValid());
        self::assertTrue($assessment->claimsValid());
        self::assertTrue($assessment->holderBindingValid());
    }

    public function testFormatProcessorContractsAreTypedAndSeparated(): void
    {
        foreach ([
            SdJwtVcCredentialProcessorInterface::class,
            IsoMdocCredentialProcessorInterface::class,
        ] as $contract) {
            $method = new \ReflectionMethod($contract, 'assess');

            self::assertSame(
                CredentialFormatProcessingAssessment::class,
                (string) $method->getReturnType()
            );
            self::assertSame(3, $method->getNumberOfParameters());
        }

        self::assertTrue(
            (new \ReflectionClass(
                HighAssuranceCredentialFormatPolicyInterface::class
            ))->isInterface()
        );
    }

    public function testFormatLayerRemainsJoseCborAndVendorNeutral(): void
    {
        foreach ([
            SdJwtVcCredentialProcessorInterface::class,
            IsoMdocCredentialProcessorInterface::class,
            HighAssuranceCredentialFormatPolicyInterface::class,
            CredentialFormatProfile::class,
            CredentialFormatProcessingContext::class,
            CredentialFormatProcessingAssessment::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('Firebase', $source);
            self::assertStringNotContainsString('openssl_', strtolower($source));
            self::assertStringNotContainsString('CBOR\Decoder', $source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('Guzzle', $source);
        }
    }
}
