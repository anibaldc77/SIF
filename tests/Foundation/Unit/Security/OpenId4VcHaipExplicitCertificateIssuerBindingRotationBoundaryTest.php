<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use OpenSSLAsymmetricKey;
use OpenSSLCertificate;
use OpenSSLCertificateSigningRequest;
use PHPUnit\Framework\TestCase;
use Sif\Adapters\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipConfiguredTrustPolicyProvider;
use Sif\Adapters\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipExplicitCertificateIssuerBindingResolver;
use Sif\Foundation\Security\Contracts\OpenId4VcHaipKeyAttestationTrustPolicyProviderInterface;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationTrustPolicy;

/**
 * C4.4B2A: certificate rotation and issuer isolation.
 *
 * All certificates and keys are generated for tests only.
 * Positive results establish signer identity, NOT full Key Attestation.
 */
final class OpenId4VcHaipExplicitCertificateIssuerBindingRotationBoundaryTest extends TestCase
{
    private const ISSUER_A = 'https://credential-a.example';
    private const ISSUER_B = 'https://credential-b.example';
    private const WALLET_A = 'https://wallet-a.example';
    private const WALLET_B = 'https://wallet-b.example';

    public function testLeafRotationUnderSameRootNeedsBothBindings(): void
    {
        $root = self::root('SIF TEST SHARED ROOT');
        $old = self::leaf($root, 'SIF OLD LEAF');
        $new = self::leaf($root, 'SIF NEW LEAF');

        $policy = self::policy(
            self::ISSUER_A,
            [$root['pem']],
            self::WALLET_A
        );

        $onlyOld = self::resolver(
            [$policy],
            [self::binding(self::ISSUER_A, $old, self::WALLET_A)]
        );

        self::assertSame(
            self::WALLET_A,
            $onlyOld->resolveAuthorizedSignerIdentifier(
                self::ISSUER_A,
                $old['jwt']
            )
        );

        self::assertNull(
            $onlyOld->resolveAuthorizedSignerIdentifier(
                self::ISSUER_A,
                $new['jwt']
            )
        );

        $both = self::resolver(
            [$policy],
            [
                self::binding(self::ISSUER_A, $old, self::WALLET_A),
                self::binding(self::ISSUER_A, $new, self::WALLET_A),
            ]
        );

        foreach ([$old, $new] as $leaf) {
            self::assertSame(
                self::WALLET_A,
                $both->resolveAuthorizedSignerIdentifier(
                    self::ISSUER_A,
                    $leaf['jwt']
                )
            );
        }
    }

    public function testRootRotationIsIndependentOfLeafBinding(): void
    {
        $oldRoot = self::root('SIF TEST OLD ROOT');
        $newRoot = self::root('SIF TEST NEW ROOT');

        $oldLeaf = self::leaf($oldRoot, 'SIF OLD ROOT LEAF');
        $newLeaf = self::leaf($newRoot, 'SIF NEW ROOT LEAF');

        $bindings = [
            self::binding(self::ISSUER_A, $oldLeaf, self::WALLET_A),
            self::binding(self::ISSUER_A, $newLeaf, self::WALLET_A),
        ];

        $beforeRootRotation = self::resolver(
            [self::policy(
                self::ISSUER_A,
                [$oldRoot['pem']],
                self::WALLET_A
            )],
            $bindings
        );

        self::assertSame(
            self::WALLET_A,
            $beforeRootRotation->resolveAuthorizedSignerIdentifier(
                self::ISSUER_A,
                $oldLeaf['jwt']
            )
        );

        self::assertNull(
            $beforeRootRotation->resolveAuthorizedSignerIdentifier(
                self::ISSUER_A,
                $newLeaf['jwt']
            )
        );

        $afterRootRotation = self::resolver(
            [self::policy(
                self::ISSUER_A,
                [$oldRoot['pem'], $newRoot['pem']],
                self::WALLET_A
            )],
            $bindings
        );

        self::assertSame(
            self::WALLET_A,
            $afterRootRotation->resolveAuthorizedSignerIdentifier(
                self::ISSUER_A,
                $newLeaf['jwt']
            )
        );
    }

    public function testSharedCertificateCannotCrossIssuerBoundary(): void
    {
        $root = self::root('SIF SHARED CERTIFICATE ROOT');

        // Deliberately omit iss: the trusted mapping is issuer-specific.
        $leaf = self::leaf(
            $root,
            'SIF SHARED LEAF',
            null
        );

        $policies = [
            self::policy(
                self::ISSUER_A,
                [$root['pem']],
                self::WALLET_A
            ),
            self::policy(
                self::ISSUER_B,
                [$root['pem']],
                self::WALLET_B
            ),
        ];

        $onlyIssuerA = self::resolver(
            $policies,
            [self::binding(
                self::ISSUER_A,
                $leaf,
                self::WALLET_A
            )]
        );

        self::assertSame(
            self::WALLET_A,
            $onlyIssuerA->resolveAuthorizedSignerIdentifier(
                self::ISSUER_A,
                $leaf['jwt']
            )
        );

        self::assertNull(
            $onlyIssuerA->resolveAuthorizedSignerIdentifier(
                self::ISSUER_B,
                $leaf['jwt']
            )
        );

        $explicitlyConfigured = self::resolver(
            $policies,
            [
                self::binding(
                    self::ISSUER_A,
                    $leaf,
                    self::WALLET_A
                ),
                self::binding(
                    self::ISSUER_B,
                    $leaf,
                    self::WALLET_B
                ),
            ]
        );

        self::assertSame(
            self::WALLET_A,
            $explicitlyConfigured->resolveAuthorizedSignerIdentifier(
                self::ISSUER_A,
                $leaf['jwt']
            )
        );

        self::assertSame(
            self::WALLET_B,
            $explicitlyConfigured->resolveAuthorizedSignerIdentifier(
                self::ISSUER_B,
                $leaf['jwt']
            )
        );
    }

    public function testPolicyProviderCannotReturnAnotherIssuerPolicy(): void
    {
        $root = self::root('SIF MISMATCHED POLICY ROOT');
        $leaf = self::leaf($root, 'SIF POLICY LEAF');

        $incorrectPolicy = self::policy(
            self::ISSUER_B,
            [$root['pem']],
            self::WALLET_A
        );

        $provider = new class ($incorrectPolicy) implements OpenId4VcHaipKeyAttestationTrustPolicyProviderInterface {
            public function __construct(
                private OpenId4VcHaipKeyAttestationTrustPolicy $policy
            ) {
            }

            public function forCredentialIssuer(
                string $credentialIssuerIdentifier
            ): ?OpenId4VcHaipKeyAttestationTrustPolicy {
                return $this->policy;
            }
        };

        $resolver = new OpenId4VcHaipExplicitCertificateIssuerBindingResolver(
            $provider,
            [self::binding(
                self::ISSUER_A,
                $leaf,
                self::WALLET_A
            )]
        );

        self::assertNull(
            $resolver->resolveAuthorizedSignerIdentifier(
                self::ISSUER_A,
                $leaf['jwt']
            )
        );
    }

    public function testSignerIdentityDoesNotEstablishJwtFreshness(): void
    {
        $root = self::root('SIF FRESHNESS BOUNDARY ROOT');

        // Deliberately old iat. This resolver does NOT assess freshness.
        $leaf = self::leaf(
            $root,
            'SIF OLD ATTESTATION',
            self::WALLET_A,
            time() - 7200
        );

        $resolver = self::resolver(
            [self::policy(
                self::ISSUER_A,
                [$root['pem']],
                self::WALLET_A
            )],
            [self::binding(
                self::ISSUER_A,
                $leaf,
                self::WALLET_A
            )]
        );

        // Identity only; C4.5 must validate temporal constraints.
        self::assertSame(
            self::WALLET_A,
            $resolver->resolveAuthorizedSignerIdentifier(
                self::ISSUER_A,
                $leaf['jwt']
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
     * @param non-empty-list<string> $anchors
     */
    private static function policy(
        string $credentialIssuer,
        array $anchors,
        string $walletIssuer
    ): OpenId4VcHaipKeyAttestationTrustPolicy {
        return new OpenId4VcHaipKeyAttestationTrustPolicy(
            $credentialIssuer,
            $anchors,
            [$walletIssuer],
            ['ES256'],
            [],
            false
        );
    }

    /**
     * @param array{jwt: string, fingerprint: string} $leaf
     * @return array{
     *     credentialIssuerIdentifier: string,
     *     certificateSha256Fingerprint: string,
     *     attestationIssuerIdentifier: string
     * }
     */
    private static function binding(
        string $credentialIssuer,
        array $leaf,
        string $walletIssuer
    ): array {
        return [
            'credentialIssuerIdentifier' => $credentialIssuer,
            'certificateSha256Fingerprint' => $leaf['fingerprint'],
            'attestationIssuerIdentifier' => $walletIssuer,
        ];
    }

    /**
     * @return array{
     *     key: OpenSSLAsymmetricKey,
     *     certificate: OpenSSLCertificate,
     *     pem: string
     * }
     */
    private static function root(string $name): array
    {
        $config = __DIR__ . '/fixtures/haip-openssl-chain-test.cnf';

        $key = @openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'prime256v1',
            'config' => $config,
        ]);

        if (!$key instanceof OpenSSLAsymmetricKey) {
            self::fail('Unable to generate test CA private key.');
        }

        $csr = @openssl_csr_new(
            ['commonName' => $name],
            $key,
            ['config' => $config, 'digest_alg' => 'sha256']
        );

        if (!$csr instanceof OpenSSLCertificateSigningRequest) {
            self::fail('Unable to create CA CSR.');
        }

        $certificate = @openssl_csr_sign(
            $csr,
            null,
            $key,
            2,
            [
                'config' => $config,
                'digest_alg' => 'sha256',
                'x509_extensions' => 'ca_ext',
            ]
        );

        if (!$certificate instanceof OpenSSLCertificate) {
            self::fail('Unable to issue test CA.');
        }

        $pem = '';

        self::assertTrue(
            openssl_x509_export($certificate, $pem)
        );

        return [
            'key' => $key,
            'certificate' => $certificate,
            'pem' => $pem,
        ];
    }

    /**
     * @param array{
     *     key: OpenSSLAsymmetricKey,
     *     certificate: OpenSSLCertificate,
     *     pem: string
     * } $root
     *
     * @return array{jwt: string, fingerprint: string}
     */
    private static function leaf(
        array $root,
        string $name,
        ?string $claimedIssuer = self::WALLET_A,
        ?int $issuedAt = null
    ): array {
        $config = __DIR__ . '/fixtures/haip-openssl-chain-test.cnf';

        $key = @openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'prime256v1',
            'config' => $config,
        ]);

        if (!$key instanceof OpenSSLAsymmetricKey) {
            self::fail('Unable to generate leaf key.');
        }

        $csr = @openssl_csr_new(
            ['commonName' => $name],
            $key,
            ['config' => $config, 'digest_alg' => 'sha256']
        );

        if (!$csr instanceof OpenSSLCertificateSigningRequest) {
            self::fail('Unable to create leaf CSR.');
        }

        $certificate = @openssl_csr_sign(
            $csr,
            $root['certificate'],
            $root['key'],
            2,
            [
                'config' => $config,
                'digest_alg' => 'sha256',
                'x509_extensions' => 'leaf_ext',
            ]
        );

        if (!$certificate instanceof OpenSSLCertificate) {
            self::fail('Unable to issue leaf certificate.');
        }

        $pem = '';

        self::assertTrue(
            openssl_x509_export($certificate, $pem)
        );

        $encoded = preg_replace(
            '/-----BEGIN CERTIFICATE-----|-----END CERTIFICATE-----|\s+/',
            '',
            $pem
        );

        self::assertIsString($encoded);

        $der = base64_decode($encoded, true);

        self::assertIsString($der);

        $header = [
            'typ' => 'key-attestation+jwt',
            'alg' => 'ES256',
            'x5c' => [$encoded],
        ];

        $claims = [
            'iat' => $issuedAt ?? time(),
            'attested_keys' => [['kty' => 'EC']],
        ];

        if ($claimedIssuer !== null) {
            $claims['iss'] = $claimedIssuer;
        }

        $signingInput = self::b64(
            (string) json_encode($header, JSON_THROW_ON_ERROR)
        ) . '.' . self::b64(
            (string) json_encode($claims, JSON_THROW_ON_ERROR)
        );

        $signature = '';

        self::assertTrue(
            openssl_sign(
                $signingInput,
                $signature,
                $key,
                OPENSSL_ALGO_SHA256
            )
        );

        return [
            'jwt' => $signingInput . '.' . self::b64(
                self::derToJose($signature)
            ),
            'fingerprint' => hash('sha256', $der),
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
