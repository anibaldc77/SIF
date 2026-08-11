<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\SdJwtVcCredentialStatusResolverInterface;
use Sif\Foundation\Security\Contracts\SdJwtVcIssuerTrustEvaluatorInterface;
use Sif\Foundation\Security\Contracts\SdJwtVcKeyBindingVerifierInterface;
use Sif\Foundation\Security\VerifiableCredentials\Formats\SdJwtVc\SdJwtVcCredentialStatus;
use Sif\Foundation\Security\VerifiableCredentials\Formats\SdJwtVc\SdJwtVcCredentialStatusAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Formats\SdJwtVc\SdJwtVcIssuerIdentity;
use Sif\Foundation\Security\VerifiableCredentials\Formats\SdJwtVc\SdJwtVcIssuerTrustAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Formats\SdJwtVc\SdJwtVcKeyBindingAssessment;
use Sif\Foundation\Security\VerifiableCredentials\Formats\SdJwtVc\SdJwtVcKeyBindingContext;

final class SdJwtVcIssuerTrustStatusAndKeyBindingTest extends TestCase
{
    public function testIssuerIdentityKeepsIdentifierAndAttributesExplicit(): void
    {
        $issuer = new SdJwtVcIssuerIdentity(
            'https://issuer.example.test',
            ['trust_framework' => 'example']
        );

        self::assertSame(
            'https://issuer.example.test',
            $issuer->identifier()
        );
        self::assertSame(
            'example',
            $issuer->attributes()['trust_framework']
        );
    }

    public function testIssuerTrustAssessmentAggregatesIdentifierAndKeyTrust(): void
    {
        $assessment = new SdJwtVcIssuerTrustAssessment(
            true,
            true,
            true
        );

        self::assertTrue($assessment->trusted());
        self::assertTrue($assessment->identifierValid());
        self::assertTrue($assessment->keyMaterialTrusted());
    }

    public function testCredentialStatusKeepsTypeAndReferenceExplicit(): void
    {
        $status = new SdJwtVcCredentialStatus(
            'status-list',
            'https://issuer.example.test/status/42'
        );

        self::assertSame('status-list', $status->statusType());
        self::assertSame(
            'https://issuer.example.test/status/42',
            $status->statusReference()
        );
    }

    public function testCredentialStatusAssessmentRejectsRevokedCredential(): void
    {
        $assessment = new SdJwtVcCredentialStatusAssessment(
            true,
            true,
            false
        );

        self::assertFalse($assessment->valid());
        self::assertTrue($assessment->revoked());
        self::assertFalse($assessment->suspended());
    }

    public function testKeyBindingAssessmentAggregatesAudienceNonceAndHolderKey(): void
    {
        $assessment = new SdJwtVcKeyBindingAssessment(
            true,
            true,
            true,
            true
        );

        self::assertTrue($assessment->valid());
        self::assertTrue($assessment->audienceValid());
        self::assertTrue($assessment->nonceValid());
        self::assertTrue($assessment->holderKeyValid());
    }

    public function testTrustStatusAndKeyBindingContractsAreTyped(): void
    {
        $issuer = new \ReflectionMethod(
            SdJwtVcIssuerTrustEvaluatorInterface::class,
            'evaluate'
        );
        $status = new \ReflectionMethod(
            SdJwtVcCredentialStatusResolverInterface::class,
            'resolve'
        );
        $keyBinding = new \ReflectionMethod(
            SdJwtVcKeyBindingVerifierInterface::class,
            'verify'
        );

        self::assertSame(
            SdJwtVcIssuerTrustAssessment::class,
            (string) $issuer->getReturnType()
        );
        self::assertSame(
            SdJwtVcCredentialStatusAssessment::class,
            (string) $status->getReturnType()
        );
        self::assertSame(
            SdJwtVcKeyBindingAssessment::class,
            (string) $keyBinding->getReturnType()
        );
    }

    public function testLayerRemainsDiscoveryTransportJwtAndTrustStoreNeutral(): void
    {
        foreach ([
            SdJwtVcIssuerTrustEvaluatorInterface::class,
            SdJwtVcCredentialStatusResolverInterface::class,
            SdJwtVcKeyBindingVerifierInterface::class,
            SdJwtVcIssuerIdentity::class,
            SdJwtVcIssuerTrustAssessment::class,
            SdJwtVcCredentialStatus::class,
            SdJwtVcCredentialStatusAssessment::class,
            SdJwtVcKeyBindingContext::class,
            SdJwtVcKeyBindingAssessment::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Guzzle', $source);
            self::assertStringNotContainsString('Firebase', $source);
            self::assertStringNotContainsString('openssl_', strtolower($source));
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
        }
    }
}
