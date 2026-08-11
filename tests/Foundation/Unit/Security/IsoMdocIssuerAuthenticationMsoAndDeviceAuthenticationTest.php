<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\IsoMdocDeviceAuthenticationVerifierInterface;
use Sif\Foundation\Security\Contracts\IsoMdocIssuerAuthenticationVerifierInterface;
use Sif\Foundation\Security\Contracts\IsoMdocMobileSecurityObjectVerifierInterface;
use Sif\Foundation\Security\VerifiableCredentials\Formats\IsoMdoc\IsoMdocDeviceAuthenticationAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Formats\IsoMdoc\IsoMdocDeviceAuthenticationContext;
use Sif\Foundation\Security\VerifiableCredentials\Formats\IsoMdoc\IsoMdocIssuerAuthenticationAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Formats\IsoMdoc\IsoMdocMobileSecurityObject;

final class IsoMdocIssuerAuthenticationMsoAndDeviceAuthenticationTest extends TestCase
{
    public function testMobileSecurityObjectKeepsValidityDigestAndDeviceKeyExplicit(): void
    {
        $mso = new IsoMdocMobileSecurityObject(
            'org.iso.18013.5.1.mDL',
            new DateTimeImmutable('2026-01-01T00:00:00Z'),
            new DateTimeImmutable('2027-01-01T00:00:00Z'),
            ['algorithm' => 'SHA-256'],
            ['key_id' => 'device-key-001']
        );

        self::assertSame(
            'org.iso.18013.5.1.mDL',
            $mso->documentType()
        );
        self::assertSame(
            'SHA-256',
            $mso->digestAlgorithmMetadata()['algorithm']
        );
        self::assertSame(
            'device-key-001',
            $mso->deviceKeyInfo()['key_id']
        );
    }

    public function testIssuerAuthenticationAssessmentAggregatesIndependentChecks(): void
    {
        $assessment = new IsoMdocIssuerAuthenticationAssessment(
            true,
            true,
            true,
            true
        );

        self::assertTrue($assessment->valid());
        self::assertTrue($assessment->signatureValid());
        self::assertTrue($assessment->certificatePathTrusted());
        self::assertTrue($assessment->msoValid());
    }

    public function testDeviceAuthenticationContextKeepsTranscriptAndKeyExplicit(): void
    {
        $context = new IsoMdocDeviceAuthenticationContext(
            'org.iso.18013.5.1.mDL',
            [
                'reader_engagement' => 'reader-data',
                'device_engagement' => 'device-data',
            ],
            'device-key-001'
        );

        self::assertSame(
            'org.iso.18013.5.1.mDL',
            $context->documentType()
        );
        self::assertSame(
            'reader-data',
            $context->sessionTranscript()['reader_engagement']
        );
        self::assertSame(
            'device-key-001',
            $context->deviceKeyId()
        );
    }

    public function testDeviceAuthenticationAssessmentAggregatesBindingChecks(): void
    {
        $assessment = new IsoMdocDeviceAuthenticationAssessment(
            true,
            true,
            true,
            true
        );

        self::assertTrue($assessment->valid());
        self::assertTrue($assessment->deviceKeyValid());
        self::assertTrue($assessment->sessionTranscriptValid());
        self::assertTrue($assessment->documentTypeBound());
    }

    public function testMsoIssuerAndDeviceAuthenticationContractsAreTyped(): void
    {
        $issuer = new \ReflectionMethod(
            IsoMdocIssuerAuthenticationVerifierInterface::class,
            'verify'
        );
        $device = new \ReflectionMethod(
            IsoMdocDeviceAuthenticationVerifierInterface::class,
            'verify'
        );
        $mso = new \ReflectionMethod(
            IsoMdocMobileSecurityObjectVerifierInterface::class,
            'verify'
        );

        self::assertSame(
            IsoMdocIssuerAuthenticationAssessment::class,
            (string) $issuer->getReturnType()
        );
        self::assertSame(
            IsoMdocDeviceAuthenticationAssessment::class,
            (string) $device->getReturnType()
        );
        self::assertSame(
            'void',
            (string) $mso->getReturnType()
        );
    }

    public function testAuthenticationLayerRemainsCoseX509AndCryptoNeutral(): void
    {
        foreach ([
            IsoMdocMobileSecurityObjectVerifierInterface::class,
            IsoMdocIssuerAuthenticationVerifierInterface::class,
            IsoMdocDeviceAuthenticationVerifierInterface::class,
            IsoMdocMobileSecurityObject::class,
            IsoMdocIssuerAuthenticationAssessment::class,
            IsoMdocDeviceAuthenticationContext::class,
            IsoMdocDeviceAuthenticationAssessment::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents((string) $reflection->getFileName());

            self::assertIsString($source);
            self::assertStringNotContainsString('COSE', $source);
            self::assertStringNotContainsString('X509', $source);
            self::assertStringNotContainsString('openssl_', strtolower($source));
            self::assertStringNotContainsString('CBOR', $source);
            self::assertStringNotContainsString('Guzzle', $source);
            self::assertStringNotContainsString('PDO', $source);
        }
    }
}
