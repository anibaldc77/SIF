<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use InvalidArgumentException;
use OpenSSLAsymmetricKey;
use OpenSSLCertificate;
use OpenSSLCertificateSigningRequest;
use PHPUnit\Framework\TestCase;
use Sif\Adapters\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipConfiguredTrustPolicyProvider;
use Sif\Adapters\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipExplicitCertificateIssuerBindingResolver;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationTrustPolicy;

final class OpenId4VcHaipExplicitCertificateIssuerBindingResolverTest extends TestCase
{
    private const CREDENTIAL_ISSUER = 'https://credential.example';
    private const ATTESTATION_ISSUER = 'https://wallet.example';

    public function testAuthenticSignerWithTrustedBindingIsResolved(): void
    {
        $fixture = self::fixture();
        $resolver = self::resolver(
            [self::policy($fixture)],
            [self::binding($fixture)]
        );

        self::assertSame(
            self::ATTESTATION_ISSUER,
            $resolver->resolveAuthorizedSignerIdentifier(
                self::CREDENTIAL_ISSUER,
                $fixture['jwt']
            )
        );
    }

    public function testMissingBindingIsRejected(): void
    {
        $fixture = self::fixture();
        $resolver = self::resolver([self::policy($fixture)], []);

        self::assertNull($resolver->resolveAuthorizedSignerIdentifier(
            self::CREDENTIAL_ISSUER,
            $fixture['jwt']
        ));
    }

    public function testMissingIssuerPolicyIsRejected(): void
    {
        $fixture = self::fixture();
        $resolver = self::resolver([], [self::binding($fixture)]);

        self::assertNull($resolver->resolveAuthorizedSignerIdentifier(
            self::CREDENTIAL_ISSUER,
            $fixture['jwt']
        ));
    }

    public function testCrossIssuerBindingIsRejected(): void
    {
        $fixture = self::fixture();

        $resolver = self::resolver(
            [self::policy($fixture)],
            [self::binding($fixture, 'https://other-credential.example')]
        );

        self::assertNull($resolver->resolveAuthorizedSignerIdentifier(
            self::CREDENTIAL_ISSUER,
            $fixture['jwt']
        ));
    }

    public function testUnauthorizedBoundIdentityIsRejected(): void
    {
        $fixture = self::fixture();

        $resolver = self::resolver(
            [self::policy($fixture)],
            [self::binding(
                $fixture,
                self::CREDENTIAL_ISSUER,
                'https://unapproved.example'
            )]
        );

        self::assertNull($resolver->resolveAuthorizedSignerIdentifier(
            self::CREDENTIAL_ISSUER,
            $fixture['jwt']
        ));
    }

    public function testTamperedSignatureIsRejected(): void
    {
        $fixture = self::fixture();
        $parts = explode('.', $fixture['jwt']);
        $parts[2] = self::b64(str_repeat("\x00", 64));

        $resolver = self::resolver(
            [self::policy($fixture)],
            [self::binding($fixture)]
        );

        self::assertNull($resolver->resolveAuthorizedSignerIdentifier(
            self::CREDENTIAL_ISSUER,
            implode('.', $parts)
        ));
    }

    public function testUntrustedCertificateChainIsRejected(): void
    {
        $fixture = self::fixture();
        $other = self::fixture();

        $resolver = self::resolver(
            [self::policy($other)],
            [self::binding($fixture)]
        );

        self::assertNull($resolver->resolveAuthorizedSignerIdentifier(
            self::CREDENTIAL_ISSUER,
            $fixture['jwt']
        ));
    }

    public function testSignedContradictoryIssuerClaimIsRejected(): void
    {
        $fixture = self::fixture('https://wrong-wallet.example');

        $resolver = self::resolver(
            [self::policy($fixture)],
            [self::binding($fixture)]
        );

        self::assertNull($resolver->resolveAuthorizedSignerIdentifier(
            self::CREDENTIAL_ISSUER,
            $fixture['jwt']
        ));
    }

    public function testOptionalIssuerClaimCanBeAbsent(): void
    {
        $fixture = self::fixture(null);

        $resolver = self::resolver(
            [self::policy($fixture)],
            [self::binding($fixture)]
        );

        self::assertSame(
            self::ATTESTATION_ISSUER,
            $resolver->resolveAuthorizedSignerIdentifier(
                self::CREDENTIAL_ISSUER,
                $fixture['jwt']
            )
        );
    }

    public function testRequiredRevocationWithoutCheckerFailsClosed(): void
    {
        $fixture = self::fixture();

        $resolver = self::resolver(
            [self::policy($fixture, true)],
            [self::binding($fixture)]
        );

        self::assertNull($resolver->resolveAuthorizedSignerIdentifier(
            self::CREDENTIAL_ISSUER,
            $fixture['jwt']
        ));
    }

    public function testRequiredAdditionalEkuWithoutCheckerFailsClosed(): void
    {
        $fixture = self::fixture();

        $resolver = self::resolver(
            [self::policy($fixture, false, ['1.3.6.1.5.5.7.3.3'])],
            [self::binding($fixture)]
        );

        self::assertNull($resolver->resolveAuthorizedSignerIdentifier(
            self::CREDENTIAL_ISSUER,
            $fixture['jwt']
        ));
    }

    public function testMalformedJwtIsRejected(): void
    {
        $fixture = self::fixture();

        $resolver = self::resolver(
            [self::policy($fixture)],
            [self::binding($fixture)]
        );

        self::assertNull($resolver->resolveAuthorizedSignerIdentifier(
            self::CREDENTIAL_ISSUER,
            'not-a-compact-jwt'
        ));
    }

    public function testDuplicateBindingIsRejected(): void
    {
        $fixture = self::fixture();
        $binding = self::binding($fixture);

        $this->expectException(InvalidArgumentException::class);

        self::resolver(
            [self::policy($fixture)],
            [$binding, $binding]
        );
    }

    public function testInvalidFingerprintIsRejected(): void
    {
        $fixture = self::fixture();
        $binding = self::binding($fixture);
        $binding['certificateSha256Fingerprint'] = 'not-a-sha256';

        $this->expectException(InvalidArgumentException::class);

        self::resolver([self::policy($fixture)], [$binding]);
    }

    public function testRenewedCertificateWithoutUpdatedBindingIsRejected(): void
    {
        $old = self::fixture();
        $renewed = self::fixture();

        $policy = new OpenId4VcHaipKeyAttestationTrustPolicy(
            self::CREDENTIAL_ISSUER,
            [$old['rootPem'], $renewed['rootPem']],
            [self::ATTESTATION_ISSUER],
            ['ES256'],
            [],
            false
        );

        $resolver = self::resolver(
            [$policy],
            [self::binding($old)]
        );

        self::assertNull($resolver->resolveAuthorizedSignerIdentifier(
            self::CREDENTIAL_ISSUER,
            $renewed['jwt']
        ));
    }

    public function testExplicitCertificateRotationIsSupported(): void
    {
        $old = self::fixture();
        $renewed = self::fixture();

        $policy = new OpenId4VcHaipKeyAttestationTrustPolicy(
            self::CREDENTIAL_ISSUER,
            [$old['rootPem'], $renewed['rootPem']],
            [self::ATTESTATION_ISSUER],
            ['ES256'],
            [],
            false
        );

        $resolver = self::resolver(
            [$policy],
            [self::binding($old), self::binding($renewed)]
        );

        self::assertSame(
            self::ATTESTATION_ISSUER,
            $resolver->resolveAuthorizedSignerIdentifier(
                self::CREDENTIAL_ISSUER,
                $old['jwt']
            )
        );

        self::assertSame(
            self::ATTESTATION_ISSUER,
            $resolver->resolveAuthorizedSignerIdentifier(
                self::CREDENTIAL_ISSUER,
                $renewed['jwt']
            )
        );
    }

    /**
     * @param list<OpenId4VcHaipKeyAttestationTrustPolicy> $policies
     * @param list<array{
     *     credentialIssuerIdentifier: string,
     *     certificateSha256Fingerprint: string,
     *     attestationIssuerIdentifier: string
     * }> $bindings
     */
    private static function resolver(
        array $policies,
        array $bindings
    ): OpenId4VcHaipExplicitCertificateIssuerBindingResolver {
        return new OpenId4VcHaipExplicitCertificateIssuerBindingResolver(
            new OpenId4VcHaipConfiguredTrustPolicyProvider($policies),
            $bindings
        );
    }

    /**
     * @param array{jwt: string, rootPem: string, leafFingerprint: string} $fixture
     * @param list<string> $eku
     */
    private static function policy(
        array $fixture,
        bool $revocationRequired = false,
        array $eku = []
    ): OpenId4VcHaipKeyAttestationTrustPolicy {
        return new OpenId4VcHaipKeyAttestationTrustPolicy(
            self::CREDENTIAL_ISSUER,
            [$fixture['rootPem']],
            [self::ATTESTATION_ISSUER],
            ['ES256'],
            $eku,
            $revocationRequired
        );
    }

    /**
     * @param array{jwt: string, rootPem: string, leafFingerprint: string} $fixture
     * @return array{
     *     credentialIssuerIdentifier: string,
     *     certificateSha256Fingerprint: string,
     *     attestationIssuerIdentifier: string
     * }
     */
    private static function binding(
        array $fixture,
        string $credentialIssuer = self::CREDENTIAL_ISSUER,
        string $attestationIssuer = self::ATTESTATION_ISSUER
    ): array {
        return [
            'credentialIssuerIdentifier' => $credentialIssuer,
            'certificateSha256Fingerprint' => $fixture['leafFingerprint'],
            'attestationIssuerIdentifier' => $attestationIssuer,
        ];
    }

    /**
     * Creates a genuine P-256 leaf certificate signed by a local test CA.
     * Keys and certificates are generated only for this unit test.
     *
     * @return array{jwt: string, rootPem: string, leafFingerprint: string}
     */
    private static function fixture(
        ?string $claimedIssuer = self::ATTESTATION_ISSUER
    ): array {
        $config = __DIR__ . '/fixtures/haip-openssl-chain-test.cnf';

        $rootKey = @openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'prime256v1',
            'config' => $config,
        ]);

        $leafKey = @openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'prime256v1',
            'config' => $config,
        ]);

        if (
            !$rootKey instanceof OpenSSLAsymmetricKey
            || !$leafKey instanceof OpenSSLAsymmetricKey
        ) {
            self::fail('Unable to generate test keys.');
        }

        $rootCsr = @openssl_csr_new(
            ['commonName' => 'SIF TEST ROOT'],
            $rootKey,
            ['config' => $config, 'digest_alg' => 'sha256']
        );

        $leafCsr = @openssl_csr_new(
            ['commonName' => 'SIF TEST LEAF'],
            $leafKey,
            ['config' => $config, 'digest_alg' => 'sha256']
        );

        if (
            !$rootCsr instanceof OpenSSLCertificateSigningRequest
            || !$leafCsr instanceof OpenSSLCertificateSigningRequest
        ) {
            self::fail('Unable to generate test CSRs.');
        }

        $rootCert = @openssl_csr_sign(
            $rootCsr,
            null,
            $rootKey,
            2,
            [
                'config' => $config,
                'digest_alg' => 'sha256',
                'x509_extensions' => 'ca_ext',
            ]
        );

        if (!$rootCert instanceof OpenSSLCertificate) {
            self::fail('Unable to sign test CA certificate.');
        }

        $leafCert = @openssl_csr_sign(
            $leafCsr,
            $rootCert,
            $rootKey,
            2,
            [
                'config' => $config,
                'digest_alg' => 'sha256',
                'x509_extensions' => 'leaf_ext',
            ]
        );

        if (!$leafCert instanceof OpenSSLCertificate) {
            self::fail('Unable to sign test leaf certificate.');
        }

        $rootPem = '';
        $leafPem = '';

        self::assertTrue(openssl_x509_export($rootCert, $rootPem));
        self::assertTrue(openssl_x509_export($leafCert, $leafPem));

        $leafBase64 = preg_replace(
            '/-----BEGIN CERTIFICATE-----|-----END CERTIFICATE-----|\s+/',
            '',
            $leafPem
        );

        self::assertIsString($leafBase64);

        $leafDer = base64_decode($leafBase64, true);
        self::assertIsString($leafDer);

        $header = [
            'typ' => 'key-attestation+jwt',
            'alg' => 'ES256',
            'x5c' => [$leafBase64],
        ];

        $claims = [
            'iat' => time(),
            'attested_keys' => [['kty' => 'EC']],
        ];

        if ($claimedIssuer !== null) {
            $claims['iss'] = $claimedIssuer;
        }

        $headerJson = json_encode($header, JSON_THROW_ON_ERROR);
        $claimsJson = json_encode($claims, JSON_THROW_ON_ERROR);

        $signingInput = self::b64($headerJson)
            . '.'
            . self::b64($claimsJson);

        $derSignature = '';

        self::assertTrue(openssl_sign(
            $signingInput,
            $derSignature,
            $leafKey,
            OPENSSL_ALGO_SHA256
        ));

        return [
            'jwt' => $signingInput . '.' . self::b64(
                self::derToJose($derSignature)
            ),
            'rootPem' => $rootPem,
            'leafFingerprint' => hash('sha256', $leafDer),
        ];
    }

    private static function derToJose(string $der): string
    {
        $offset = 0;

        self::assertSame(0x30, ord($der[$offset++]));

        $length = ord($der[$offset++]);

        if (($length & 0x80) !== 0) {
            $count = $length & 0x7f;
            $length = 0;

            for ($i = 0; $i < $count; $i++) {
                $length = ($length << 8) | ord($der[$offset++]);
            }
        }

        self::assertSame(strlen($der) - $offset, $length);

        $parts = [];

        for ($i = 0; $i < 2; $i++) {
            self::assertSame(0x02, ord($der[$offset++]));

            $componentLength = ord($der[$offset++]);
            $component = ltrim(
                substr($der, $offset, $componentLength),
                "\x00"
            );

            $offset += $componentLength;

            self::assertLessThanOrEqual(32, strlen($component));

            $parts[] = str_pad(
                $component,
                32,
                "\x00",
                STR_PAD_LEFT
            );
        }

        self::assertSame(strlen($der), $offset);

        return implode('', $parts);
    }

    private static function b64(string $value): string
    {
        return rtrim(
            strtr(base64_encode($value), '+/', '-_'),
            '='
        );
    }
}
