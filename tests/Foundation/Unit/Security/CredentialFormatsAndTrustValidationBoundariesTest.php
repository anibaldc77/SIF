<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\CredentialFormatHandlerInterface;
use Sif\Foundation\Security\Contracts\CredentialFormatRegistryInterface;
use Sif\Foundation\Security\Contracts\CredentialIssuerTrustResolverInterface;
use Sif\Foundation\Security\Contracts\CredentialTrustPolicyInterface;
use Sif\Foundation\Security\VerifiableCredentials\CredentialEnvelope;
use Sif\Foundation\Security\VerifiableCredentials\CredentialFormat;
use Sif\Foundation\Security\VerifiableCredentials\CredentialTrustAssessment;
use Sif\Foundation\Security\VerifiableCredentials\CredentialTrustContext;
use Sif\Foundation\Security\VerifiableCredentials\VerifiableCredential;

final class CredentialFormatsAndTrustValidationBoundariesTest extends TestCase
{
    public function testCredentialFormatIsOpaqueAndExtensible(): void
    {
        $format = new CredentialFormat('vc+sd-jwt');

        self::assertSame('vc+sd-jwt', $format->value());
    }

    public function testCredentialEnvelopeKeepsFormatAndSerializedValueExplicit(): void
    {
        $envelope = new CredentialEnvelope(
            new CredentialFormat('mso_mdoc'),
            'serialized-credential'
        );

        self::assertSame('mso_mdoc', $envelope->format()->value());
        self::assertSame(
            'serialized-credential',
            $envelope->serializedCredential()
        );
    }

    public function testTrustContextKeepsIssuerTypeAndValidationRequirementsExplicit(): void
    {
        $context = new CredentialTrustContext(
            new DateTimeImmutable('2026-08-10T13:30:00Z'),
            ['https://issuer.example.test'],
            ['IdentityCredential'],
            true,
            true
        );

        self::assertSame(
            ['https://issuer.example.test'],
            $context->trustedIssuers()
        );
        self::assertSame(
            ['IdentityCredential'],
            $context->acceptedCredentialTypes()
        );
        self::assertTrue($context->requireSignatureValidation());
        self::assertTrue($context->requireValidityWindow());
    }

    public function testTrustAssessmentSeparatesViolationsAndWarnings(): void
    {
        $assessment = new CredentialTrustAssessment(
            false,
            ['issuer_not_trusted'],
            ['status_not_checked']
        );

        self::assertFalse($assessment->trusted());
        self::assertSame(
            ['issuer_not_trusted'],
            $assessment->violations()
        );
        self::assertSame(
            ['status_not_checked'],
            $assessment->warnings()
        );
    }

    public function testFormatAndTrustContractsAreTyped(): void
    {
        $parse = new \ReflectionMethod(
            CredentialFormatHandlerInterface::class,
            'parse'
        );
        $handler = new \ReflectionMethod(
            CredentialFormatRegistryInterface::class,
            'handler'
        );
        $assess = new \ReflectionMethod(
            CredentialTrustPolicyInterface::class,
            'assess'
        );

        self::assertSame(
            VerifiableCredential::class,
            (string) $parse->getReturnType()
        );
        self::assertSame(
            CredentialFormatHandlerInterface::class,
            (string) $handler->getReturnType()
        );
        self::assertSame(
            CredentialTrustAssessment::class,
            (string) $assess->getReturnType()
        );

        self::assertTrue(
            (new \ReflectionClass(
                CredentialIssuerTrustResolverInterface::class
            ))->isInterface()
        );
    }

    public function testCredentialFormatAndTrustLayerRemainsCryptoAndFormatImplementationNeutral(): void
    {
        foreach ([
            CredentialFormatHandlerInterface::class,
            CredentialFormatRegistryInterface::class,
            CredentialTrustPolicyInterface::class,
            CredentialIssuerTrustResolverInterface::class,
            CredentialFormat::class,
            CredentialEnvelope::class,
            CredentialTrustContext::class,
            CredentialTrustAssessment::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('openssl_', strtolower($source));
            self::assertStringNotContainsString('Firebase', $source);
            self::assertStringNotContainsString('Lcobucci', $source);
            self::assertStringNotContainsString('CBOR', $source);
            self::assertStringNotContainsString('JSON-LD', $source);
            self::assertStringNotContainsString('PDO', $source);
        }
    }
}
