<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use OpenSSLAsymmetricKey;
use OpenSSLCertificate;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipExplicitX509TrustEvaluator;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipUnverifiedKeyAttestationJwt;

final class OpenId4VcHaipExplicitX509TrustEvaluatorTest extends TestCase
{
    public function testAcceptsLeafSignedDirectlyByExplicitRoot(): void
    {
        [$root, , $leaf] = self::chain(false);
        self::assertTrue(self::chainMatchesForTest([$leaf], [$root]));
    }

    public function testAcceptsValidLeafIntermediateRootPath(): void
    {
        [$root, $intermediate, $leaf] = self::chain();
        self::assertTrue(self::chainMatchesForTest([$leaf, $intermediate], [$root]));
    }

    public function testRejectsMissingTrustedAnchors(): void
    {
        [, , $leaf] = self::chain(false);
        self::assertFalse(self::chainMatchesForTest([$leaf], []));
    }

    public function testRejectsRootNotConfiguredAsTrusted(): void
    {
        [, $intermediate, $leaf] = self::chain();
        [$differentRoot] = self::chain(false);
        self::assertFalse(self::chainMatchesForTest([$leaf, $intermediate], [$differentRoot]));
    }

    public function testRejectsTrustAnchorIncludedInX5c(): void
    {
        [$root, $intermediate, $leaf] = self::chain();
        self::assertFalse(self::chainMatchesForTest([$leaf, $intermediate, $root], [$root]));
    }

    public function testRejectsSelfSignedSigningCertificateEvenWhenConfiguredAsAnchor(): void
    {
        [$root] = self::chain(false);
        self::assertFalse(self::chainMatchesForTest([$root], [$root]));
    }

    public function testRejectsMissingIntermediateCertificate(): void
    {
        [$root, , $leaf] = self::chain();
        self::assertFalse(self::chainMatchesForTest([$leaf], [$root]));
    }

    public function testRejectsReversedX5cOrder(): void
    {
        [$root, $intermediate, $leaf] = self::chain();
        self::assertFalse(self::chainMatchesForTest([$intermediate, $leaf], [$root]));
    }

    public function testRejectsDuplicateCertificateInX5c(): void
    {
        [$root, $intermediate, $leaf] = self::chain();
        self::assertFalse(self::chainMatchesForTest([$leaf, $intermediate, $intermediate], [$root]));
    }

    public function testRejectsIssuerWithoutCaCertificateCapabilities(): void
    {
        [$root, $invalidIntermediate, $leaf] = self::chain(true, 'bad_ca_ext');
        self::assertFalse(self::chainMatchesForTest([$leaf, $invalidIntermediate], [$root]));
    }

    public function testRejectsLeafWithCaPrivileges(): void
    {
        [$root, $intermediate, $leaf] = self::chain(true, 'intermediate_ext', 'ca_ext');
        self::assertFalse(self::chainMatchesForTest([$leaf, $intermediate], [$root]));
    }

    public function testRejectsMismatchedIntermediateSigningKey(): void
    {
        [$root, , $leaf] = self::chain();
        [, $otherIntermediate] = self::chain();
        self::assertFalse(self::chainMatchesForTest([$leaf, $otherIntermediate], [$root]));
    }

    public function testRejectsInvalidAnchorPem(): void
    {
        [, , $leaf] = self::chain(false);
        self::assertFalse(self::chainMatchesForTest([$leaf], ['not-a-certificate']));
    }

    /**
     * @param list<string> $x5cCertificates
     * @param list<string> $anchorCertificates
     */
    private static function chainMatchesForTest(array $x5cCertificates, array $anchorCertificates): bool
    {
        $header = [
            'typ' => 'key-attestation+jwt',
            'alg' => 'ES256',
            'x5c' => array_map(self::pemBase64(...), $x5cCertificates),
        ];
        $claims = ['iat' => time(), 'attested_keys' => [['kty' => 'EC']]];
        $jsonHeader = json_encode($header, JSON_THROW_ON_ERROR);
        $jsonClaims = json_encode($claims, JSON_THROW_ON_ERROR);
        $jwt = self::b64($jsonHeader) . '.' . self::b64($jsonClaims) . '.' . self::b64(str_repeat("\x01", 64));
        return (new OpenId4VcHaipExplicitX509TrustEvaluator())->chainMatchesTrustedAnchors(
            OpenId4VcHaipUnverifiedKeyAttestationJwt::parse($jwt),
            $anchorCertificates
        );
    }

    /** @return array{string, string, string} */
    private static function chain(
        bool $withIntermediate = true,
        string $intermediateExtension = 'intermediate_ext',
        string $leafExtension = 'leaf_ext'
    ): array {
        [$rootKey, $rootCert, $rootPem] = self::issue('SIF HAIP TEST ROOT', null, null, 'ca_ext');
        if (!$withIntermediate) {
            [, , $leafPem] = self::issue('SIF HAIP TEST LEAF', $rootCert, $rootKey, $leafExtension);
            return [$rootPem, '', $leafPem];
        }
        [$intermediateKey, $intermediateCert, $intermediatePem] = self::issue(
            'SIF HAIP TEST INTERMEDIATE', $rootCert, $rootKey, $intermediateExtension
        );
        [, , $leafPem] = self::issue(
            'SIF HAIP TEST LEAF', $intermediateCert, $intermediateKey, $leafExtension
        );
        return [$rootPem, $intermediatePem, $leafPem];
    }

    /** @return array{OpenSSLAsymmetricKey, OpenSSLCertificate, string} */
    private static function issue(
        string $name,
        ?OpenSSLCertificate $issuer,
        ?OpenSSLAsymmetricKey $issuerPrivateKey,
        string $extension
    ): array {
        $config = __DIR__ . '/fixtures/haip-openssl-chain-test.cnf';
        $privateKey = @openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'prime256v1',
            'config' => $config,
        ]);
        if (!$privateKey instanceof OpenSSLAsymmetricKey) {
            $opensslErrors = [];
            while (($opensslError = openssl_error_string()) !== false) {
                $opensslErrors[] = $opensslError;
            }
            self::fail('Could not generate test private key. OpenSSL: ' . implode(' | ', $opensslErrors));
        }
        $csr = @openssl_csr_new(
            ['commonName' => $name], $privateKey, ['config' => $config, 'digest_alg' => 'sha256']
        );
        if (!$csr instanceof \OpenSSLCertificateSigningRequest) {
            self::fail('Could not generate test CSR.');
        }
        $certificate = @openssl_csr_sign(
            $csr,
            $issuer,
            $issuerPrivateKey ?? $privateKey,
            2,
            ['config' => $config, 'digest_alg' => 'sha256', 'x509_extensions' => $extension]
        );
        if (!$certificate instanceof OpenSSLCertificate) {
            self::fail('Could not generate test certificate.');
        }
        $pem = '';
        if (!openssl_x509_export($certificate, $pem)) {
            self::fail('Could not export test certificate.');
        }
        return [$privateKey, $certificate, $pem];
    }

    private static function pemBase64(string $pem): string
    {
        $result = preg_replace('/-----BEGIN CERTIFICATE-----|-----END CERTIFICATE-----|\s+/', '', $pem);
        if (!is_string($result)) {
            self::fail('Could not encode test certificate.');
        }
        return $result;
    }

    private static function b64(string $text): string
    {
        return rtrim(strtr(base64_encode($text), '+/', '-_'), '=');
    }
}
