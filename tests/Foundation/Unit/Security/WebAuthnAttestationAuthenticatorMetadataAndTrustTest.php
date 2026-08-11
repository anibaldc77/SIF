<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\WebAuthnAttestationStatementParserInterface;
use Sif\Foundation\Security\Contracts\WebAuthnAttestationTrustPolicyInterface;
use Sif\Foundation\Security\Contracts\WebAuthnAttestationVerifierInterface;
use Sif\Foundation\Security\Contracts\WebAuthnAuthenticatorMetadataResolverInterface;
use Sif\Foundation\Security\WebAuthn\WebAuthnAttestationStatement;
use Sif\Foundation\Security\WebAuthn\WebAuthnAttestationTrustAssessment;
use Sif\Foundation\Security\WebAuthn\WebAuthnAttestationTrustContext;
use Sif\Foundation\Security\WebAuthn\WebAuthnAuthenticatorMetadata;

final class WebAuthnAttestationAuthenticatorMetadataAndTrustTest extends TestCase
{
    public function testAttestationStatementKeepsFormatAndAttributesExplicit(): void
    {
        $statement = new WebAuthnAttestationStatement(
            'packed',
            'serialized-attestation',
            ['alg' => -7]
        );

        self::assertSame('packed', $statement->format());
        self::assertSame(
            'serialized-attestation',
            $statement->serializedStatement()
        );
        self::assertSame(-7, $statement->attributes()['alg']);
    }

    public function testAuthenticatorMetadataKeepsTrustRelevantDataExplicit(): void
    {
        $metadata = new WebAuthnAuthenticatorMetadata(
            'aaguid-001',
            'Platform Authenticator',
            ['FIDO-L2'],
            ['ES256'],
            ['basic_full']
        );

        self::assertSame(
            'aaguid-001',
            $metadata->authenticatorIdentifier()
        );
        self::assertSame(
            'Platform Authenticator',
            $metadata->description()
        );
        self::assertSame(
            ['FIDO-L2'],
            $metadata->certificationDescriptors()
        );
        self::assertSame(
            ['ES256'],
            $metadata->authenticationAlgorithms()
        );
        self::assertSame(
            ['basic_full'],
            $metadata->attestationTypes()
        );
    }

    public function testTrustContextKeepsRequirementsExplicit(): void
    {
        $context = new WebAuthnAttestationTrustContext(
            true,
            ['packed', 'tpm'],
            ['FIDO-L2'],
            true
        );

        self::assertTrue($context->attestationRequired());
        self::assertSame(
            ['packed', 'tpm'],
            $context->allowedAttestationFormats()
        );
        self::assertSame(
            ['FIDO-L2'],
            $context->requiredCertifications()
        );
        self::assertTrue($context->metadataRequired());
    }

    public function testTrustAssessmentAggregatesAttestationAndMetadata(): void
    {
        $assessment = new WebAuthnAttestationTrustAssessment(
            true,
            true,
            true
        );

        self::assertTrue($assessment->trusted());
        self::assertTrue($assessment->attestationValid());
        self::assertTrue($assessment->metadataTrusted());
    }

    public function testAttestationContractsAreTyped(): void
    {
        $parser = new \ReflectionMethod(
            WebAuthnAttestationStatementParserInterface::class,
            'parse'
        );
        $resolver = new \ReflectionMethod(
            WebAuthnAuthenticatorMetadataResolverInterface::class,
            'resolve'
        );
        $policy = new \ReflectionMethod(
            WebAuthnAttestationTrustPolicyInterface::class,
            'assess'
        );

        self::assertSame(
            WebAuthnAttestationStatement::class,
            (string) $parser->getReturnType()
        );
        self::assertSame(
            '?' . WebAuthnAuthenticatorMetadata::class,
            (string) $resolver->getReturnType()
        );
        self::assertSame(
            WebAuthnAttestationTrustAssessment::class,
            (string) $policy->getReturnType()
        );

        self::assertTrue(
            (new \ReflectionClass(
                WebAuthnAttestationVerifierInterface::class
            ))->isInterface()
        );
    }

    public function testAttestationLayerRemainsMetadataCryptoAndTransportNeutral(): void
    {
        foreach ([
            WebAuthnAttestationStatementParserInterface::class,
            WebAuthnAuthenticatorMetadataResolverInterface::class,
            WebAuthnAttestationTrustPolicyInterface::class,
            WebAuthnAttestationVerifierInterface::class,
            WebAuthnAttestationStatement::class,
            WebAuthnAuthenticatorMetadata::class,
            WebAuthnAttestationTrustContext::class,
            WebAuthnAttestationTrustAssessment::class,
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
            self::assertStringNotContainsString('openssl_', strtolower($source));
            self::assertStringNotContainsString('FIDO MDS', $source);
        }
    }
}
