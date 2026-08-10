<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\IdentityAssuranceClaimsMapperInterface;
use Sif\Foundation\Security\Contracts\SelectiveDisclosurePolicyInterface;
use Sif\Foundation\Security\Contracts\VerifiableCredentialVerifierInterface;
use Sif\Foundation\Security\Contracts\VerifiablePresentationVerifierInterface;
use Sif\Foundation\Security\VerifiableCredentials\IdentityAssuranceEvidence;
use Sif\Foundation\Security\VerifiableCredentials\VerifiableCredential;
use Sif\Foundation\Security\VerifiableCredentials\VerifiableCredentialSubject;
use Sif\Foundation\Security\VerifiableCredentials\VerifiablePresentation;
use Sif\Foundation\Security\VerifiableCredentials\VerifiedIdentityClaims;

final class VerifiableCredentialsAndHighAssuranceIdentityPresentationArchitectureTest extends TestCase
{
    public function testCredentialSubjectKeepsClaimsExplicit(): void
    {
        $subject = new VerifiableCredentialSubject(
            'user-001',
            ['given_name' => 'Alice']
        );

        self::assertSame('user-001', $subject->subjectId());
        self::assertSame('Alice', $subject->claims()['given_name']);
    }

    public function testCredentialKeepsIssuerSubjectAndTypesExplicit(): void
    {
        $credential = new VerifiableCredential(
            'https://issuer.example.test',
            new VerifiableCredentialSubject('user-001'),
            ['IdentityCredential']
        );

        self::assertSame(
            'https://issuer.example.test',
            $credential->issuer()
        );
        self::assertSame('user-001', $credential->subject()->subjectId());
        self::assertSame(['IdentityCredential'], $credential->types());
    }

    public function testPresentationKeepsHolderCredentialsNonceAndAudienceExplicit(): void
    {
        $presentation = new VerifiablePresentation(
            'wallet-holder-001',
            [$this->credential()],
            'nonce-001',
            'verifier-001'
        );

        self::assertSame('wallet-holder-001', $presentation->holder());
        self::assertCount(1, $presentation->credentials());
        self::assertSame('nonce-001', $presentation->nonce());
        self::assertSame('verifier-001', $presentation->audience());
    }

    public function testVerifiedClaimsCanIncludeAssuranceEvidence(): void
    {
        $claims = new VerifiedIdentityClaims(
            ['given_name' => 'Alice'],
            [
                new IdentityAssuranceEvidence(
                    'identity-document',
                    'document-verification'
                ),
            ]
        );

        self::assertSame('Alice', $claims->claims()['given_name']);
        self::assertCount(1, $claims->evidence());
    }

    public function testVerificationAndMappingContractsAreTyped(): void
    {
        $credential = new \ReflectionMethod(
            VerifiableCredentialVerifierInterface::class,
            'verify'
        );
        $presentation = new \ReflectionMethod(
            VerifiablePresentationVerifierInterface::class,
            'verify'
        );
        $mapper = new \ReflectionMethod(
            IdentityAssuranceClaimsMapperInterface::class,
            'map'
        );

        self::assertSame(
            VerifiableCredential::class,
            (string) $credential->getReturnType()
        );
        self::assertSame(
            VerifiablePresentation::class,
            (string) $presentation->getReturnType()
        );
        self::assertSame(
            VerifiedIdentityClaims::class,
            (string) $mapper->getReturnType()
        );
    }

    public function testArchitectureRemainsFormatCryptoAndWalletNeutral(): void
    {
        foreach ([
            VerifiableCredentialVerifierInterface::class,
            VerifiablePresentationVerifierInterface::class,
            IdentityAssuranceClaimsMapperInterface::class,
            SelectiveDisclosurePolicyInterface::class,
            VerifiableCredential::class,
            VerifiablePresentation::class,
            VerifiedIdentityClaims::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents((string) $reflection->getFileName());

            self::assertIsString($source);
            self::assertStringNotContainsString('openssl_', strtolower($source));
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('Firebase', $source);
        }
    }

    private function credential(): VerifiableCredential
    {
        return new VerifiableCredential(
            'https://issuer.example.test',
            new VerifiableCredentialSubject('user-001'),
            ['IdentityCredential']
        );
    }
}
