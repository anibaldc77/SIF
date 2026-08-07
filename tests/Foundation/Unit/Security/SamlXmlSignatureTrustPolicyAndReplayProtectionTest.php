<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\SamlReplayStoreInterface;
use Sif\Foundation\Security\Contracts\SamlTrustStoreInterface;
use Sif\Foundation\Security\Contracts\SamlXmlSignatureVerifierInterface;
use Sif\Foundation\Security\Exceptions\SamlReplayDetectedException;
use Sif\Foundation\Security\Saml\SamlCertificateFingerprint;
use Sif\Foundation\Security\Saml\SamlEntityId;
use Sif\Foundation\Security\Saml\SamlReplayGuard;
use Sif\Foundation\Security\Saml\SamlSignaturePolicyValidator;
use Sif\Foundation\Security\Saml\SamlSignatureTrustValidator;
use Sif\Foundation\Security\Saml\SamlSignatureValidationContext;
use Sif\Foundation\Security\Saml\SamlSignedDocumentPolicy;
use Sif\Foundation\Security\Saml\SamlXmlSignatureVerificationResult;

final class SamlXmlSignatureTrustPolicyAndReplayProtectionTest extends TestCase
{
    public function testTrustedCertificateAndValidSignatureAreAccepted(): void
    {
        $validator = new SamlSignatureTrustValidator(
            new StaticSamlTrustStore(true),
            new StaticSamlSignatureVerifier(true)
        );

        $result = $validator->validate(
            new SamlEntityId('https://idp.example/saml'),
            '<signed-document/>',
            $this->fingerprint()
        );

        self::assertTrue($result->verified());
        self::assertSame([], $result->violations());
    }

    public function testUntrustedCertificateFailsBeforeSignatureVerifier(): void
    {
        $verifier = new CountingSamlSignatureVerifier();

        $validator = new SamlSignatureTrustValidator(
            new StaticSamlTrustStore(false),
            $verifier
        );

        $result = $validator->validate(
            new SamlEntityId('https://idp.example/saml'),
            '<signed-document/>',
            $this->fingerprint()
        );

        self::assertFalse($result->verified());
        self::assertContains(
            'certificate_not_trusted',
            $result->violations()
        );
        self::assertSame(0, $verifier->calls());
    }

    public function testSignedDocumentPolicyCanRequireBothResponseAndAssertion(): void
    {
        $validator = new SamlSignaturePolicyValidator(
            new SamlSignedDocumentPolicy(
                requireSignedResponse: true,
                requireSignedAssertion: true
            ),
            new SamlSignatureTrustValidator(
                new StaticSamlTrustStore(true),
                new StaticSamlSignatureVerifier(true)
            )
        );

        $result = $validator->validate(
            new SamlSignatureValidationContext(
                new SamlEntityId('https://idp.example/saml'),
                $this->fingerprint(),
                '<Response/>',
                '<Assertion/>'
            )
        );

        self::assertTrue($result->verified());
    }

    public function testMissingAssertionFailsWhenSignedAssertionIsRequired(): void
    {
        $validator = new SamlSignaturePolicyValidator(
            new SamlSignedDocumentPolicy(
                requireSignedResponse: true,
                requireSignedAssertion: true
            ),
            new SamlSignatureTrustValidator(
                new StaticSamlTrustStore(true),
                new StaticSamlSignatureVerifier(true)
            )
        );

        $result = $validator->validate(
            new SamlSignatureValidationContext(
                new SamlEntityId('https://idp.example/saml'),
                $this->fingerprint(),
                '<Response/>',
                null
            )
        );

        self::assertFalse($result->verified());
        self::assertContains(
            'signed_assertion_required',
            $result->violations()
        );
    }

    public function testReplayGuardRejectsRepeatedIdentifier(): void
    {
        $guard = new SamlReplayGuard(
            new InMemorySamlReplayStore()
        );

        $expiresAt = new DateTimeImmutable(
            '2026-08-07T23:30:00Z'
        );

        $guard->assertFresh(
            '_response-000000000001',
            $expiresAt
        );

        $this->expectException(
            SamlReplayDetectedException::class
        );

        $guard->assertFresh(
            '_response-000000000001',
            $expiresAt
        );
    }

    public function testTrustReplayAndSignatureContractsRemainInfrastructureNeutral(): void
    {
        foreach ([
            SamlTrustStoreInterface::class,
            SamlReplayStoreInterface::class,
            SamlXmlSignatureVerifierInterface::class,
        ] as $class) {
            $reflection = new \ReflectionClass($class);
            $source = file_get_contents(
                (string) $reflection->getFileName()
            );

            self::assertIsString($source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('curl_', strtolower($source));
            self::assertStringNotContainsString('Keycloak', $source);
            self::assertStringNotContainsString('OneLogin', $source);
        }
    }

    private function fingerprint(): SamlCertificateFingerprint
    {
        return new SamlCertificateFingerprint(
            str_repeat('ab', 32)
        );
    }
}

final readonly class StaticSamlTrustStore implements SamlTrustStoreInterface
{
    public function __construct(private bool $trusted)
    {
    }

    public function trusts(
        SamlEntityId $entityId,
        SamlCertificateFingerprint $fingerprint
    ): bool {
        return $this->trusted;
    }
}

final readonly class StaticSamlSignatureVerifier implements SamlXmlSignatureVerifierInterface
{
    public function __construct(private bool $verified)
    {
    }

    public function verify(
        string $xml,
        SamlCertificateFingerprint $expectedFingerprint
    ): SamlXmlSignatureVerificationResult {
        return $this->verified
            ? SamlXmlSignatureVerificationResult::success()
            : SamlXmlSignatureVerificationResult::failed([
                'signature_invalid',
            ]);
    }
}

final class CountingSamlSignatureVerifier implements SamlXmlSignatureVerifierInterface
{
    private int $calls = 0;

    public function verify(
        string $xml,
        SamlCertificateFingerprint $expectedFingerprint
    ): SamlXmlSignatureVerificationResult {
        $this->calls++;

        return SamlXmlSignatureVerificationResult::success();
    }

    public function calls(): int
    {
        return $this->calls;
    }
}

final class InMemorySamlReplayStore implements SamlReplayStoreInterface
{
    /** @var array<string,DateTimeImmutable> */
    private array $identifiers = [];

    public function contains(string $identifier): bool
    {
        return isset($this->identifiers[$identifier]);
    }

    public function remember(
        string $identifier,
        DateTimeImmutable $expiresAt
    ): void {
        $this->identifiers[$identifier] = $expiresAt;
    }
}
