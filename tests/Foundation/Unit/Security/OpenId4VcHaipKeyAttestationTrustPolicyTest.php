<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Sif\Foundation\Security\Contracts\OpenId4VcHaipKeyAttestationTrustPolicyProviderInterface;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationTrustPolicy;

final class OpenId4VcHaipKeyAttestationTrustPolicyTest extends TestCase
{
    public function testDescribesRequirementsWithoutVerifyingAnyEvidence(): void
    {
        $pem = self::anchor('test-only-anchor');
        $policy = new OpenId4VcHaipKeyAttestationTrustPolicy(
            'https://credential.example',
            [$pem],
            ['https://wallet-provider.example'],
            ['ES256'],
            ['1.3.6.1.5.5.7.3.3'],
            true,
            600,
            15
        );

        self::assertSame('https://credential.example', $policy->credentialIssuerIdentifier());
        self::assertSame([$pem], $policy->trustedAnchorPems());
        self::assertSame(['https://wallet-provider.example'], $policy->authorizedAttestationIssuerIdentifiers());
        self::assertSame(['ES256'], $policy->permittedAlgorithms());
        self::assertSame(['1.3.6.1.5.5.7.3.3'], $policy->requiredExtendedKeyUsageOids());
        self::assertTrue($policy->digitalSignatureKeyUsageRequired());
        self::assertTrue($policy->certificateValidityRequired());
        self::assertTrue($policy->revocationCheckRequired());
        self::assertSame(600, $policy->maximumAttestationAgeSeconds());
        self::assertSame(15, $policy->allowedClockSkewSeconds());
    }

    public function testPolicyIsReadonly(): void
    {
        self::assertTrue((new ReflectionClass(OpenId4VcHaipKeyAttestationTrustPolicy::class))->isReadOnly());
    }

    public function testRejectsMissingTrustAnchors(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new OpenId4VcHaipKeyAttestationTrustPolicy('credential', [], ['provider'], ['ES256']);
    }

    public function testRejectsMalformedTrustAnchorEncoding(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new OpenId4VcHaipKeyAttestationTrustPolicy('credential', ['untrusted bytes'], ['provider'], ['ES256']);
    }

    public function testRejectsDuplicateTrustAnchorRegardlessOfPemWhitespace(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $pem = self::anchor('duplicate-anchor');
        new OpenId4VcHaipKeyAttestationTrustPolicy('credential', [$pem, rtrim($pem)], ['provider'], ['ES256']);
    }

    public function testRejectsBlankCredentialIssuer(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new OpenId4VcHaipKeyAttestationTrustPolicy(' ', [self::anchor('root')], ['provider'], ['ES256']);
    }

    public function testRejectsMissingAuthorizedAttestationIssuer(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new OpenId4VcHaipKeyAttestationTrustPolicy('credential', [self::anchor('root')], [], ['ES256']);
    }

    public function testRejectsDuplicateAuthorizedAttestationIssuer(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new OpenId4VcHaipKeyAttestationTrustPolicy('credential', [self::anchor('root')], ['provider', 'provider'], ['ES256']);
    }

    public function testRejectsWhitespacePaddedAttestationIssuer(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new OpenId4VcHaipKeyAttestationTrustPolicy('credential', [self::anchor('root')], [' provider '], ['ES256']);
    }

    public function testRejectsMissingAlgorithms(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new OpenId4VcHaipKeyAttestationTrustPolicy('credential', [self::anchor('root')], ['provider'], []);
    }

    public function testRejectsUnimplementedOrUnsafeAlgorithms(): void
    {
        foreach (['none', 'HS256', 'ES384'] as $algorithm) {
            try {
                new OpenId4VcHaipKeyAttestationTrustPolicy(
                    'credential', [self::anchor('root')], ['provider'], [$algorithm]
                );
                self::fail('Unsupported algorithm was accepted: ' . $algorithm);
            } catch (InvalidArgumentException) {
                self::assertTrue(true);
            }
        }
    }

    public function testRejectsInvalidExtendedKeyUsageOid(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new OpenId4VcHaipKeyAttestationTrustPolicy(
            'credential', [self::anchor('root')], ['provider'], ['ES256'], ['codeSigning']
        );
    }

    public function testRejectsDuplicateExtendedKeyUsageOids(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new OpenId4VcHaipKeyAttestationTrustPolicy(
            'credential', [self::anchor('root')], ['provider'], ['ES256'], ['1.3.6.1.5.5.7.3.3', '1.3.6.1.5.5.7.3.3']
        );
    }

    public function testRejectsInvalidMaximumAttestationAge(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new OpenId4VcHaipKeyAttestationTrustPolicy(
            'credential', [self::anchor('root')], ['provider'], ['ES256'], [], true, 0
        );
    }

    public function testRejectsInvalidClockSkew(): void
    {
        $this->expectException(InvalidArgumentException::class);
        new OpenId4VcHaipKeyAttestationTrustPolicy(
            'credential', [self::anchor('root')], ['provider'], ['ES256'], [], true, 300, -1
        );
    }

    public function testExplicitEcosystemRevocationOptionIsOnlyARequirementNotEvidence(): void
    {
        $policy = new OpenId4VcHaipKeyAttestationTrustPolicy(
            'credential', [self::anchor('root')], ['provider'], ['ES256'], [], false
        );
        self::assertFalse($policy->revocationCheckRequired());
        self::assertSame([], $policy->requiredExtendedKeyUsageOids());
    }

    public function testProviderCanDenyMissingIssuerPolicyWithoutDefaultFallback(): void
    {
        $provider = new class implements OpenId4VcHaipKeyAttestationTrustPolicyProviderInterface {
            public function forCredentialIssuer(string $credentialIssuerIdentifier): ?OpenId4VcHaipKeyAttestationTrustPolicy
            {
                return null;
            }
        };

        self::assertNull($provider->forCredentialIssuer('unconfigured-credential-issuer'));
    }

    private static function anchor(string $testOnlyDer): string
    {
        // Intentionally not a real certificate: Foundation checks only PEM encoding.
        // The specialized X.509 adapter must validate the actual certificate.
        return "-----BEGIN CERTIFICATE-----\n" . base64_encode($testOnlyDer) . "\n-----END CERTIFICATE-----\n";
    }
}
