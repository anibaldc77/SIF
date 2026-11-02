<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use OpenSSLAsymmetricKey;
use OpenSSLCertificate;
use OpenSSLCertificateSigningRequest;
use PHPUnit\Framework\TestCase;
use Sif\Adapters\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipExplicitX509TrustEvaluator as AdapterEvaluator;
use Sif\Adapters\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipUnverifiedKeyAttestationJwt as AdapterJwt;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipExplicitX509TrustEvaluator as LegacyEvaluator;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipUnverifiedKeyAttestationJwt as LegacyJwt;

final class OpenId4VcHaipAdapterX509TrustEvaluatorParityTest extends TestCase
{
    public function testSourcesDifferOnlyByNamespace(): void
    {
        $root = dirname(__DIR__, 4);

        $legacy = file_get_contents(
            $root . '/src/Foundation/Security/VerifiableCredentials/Haip/Issuance/OpenId4VcHaipExplicitX509TrustEvaluator.php'
        );

        $adapter = file_get_contents(
            $root . '/src/Adapters/Security/VerifiableCredentials/Haip/Issuance/OpenId4VcHaipExplicitX509TrustEvaluator.php'
        );

        self::assertIsString($legacy);
        self::assertIsString($adapter);

        $normalized = str_replace(
            'namespace Sif\Adapters\Security\VerifiableCredentials\Haip\Issuance;',
            'namespace Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance;',
            $adapter
        );

        self::assertSame($legacy, $normalized);
    }

    public function testAcceptsLeafSignedDirectlyByExplicitRoot(): void
    {
        [$root, , $leaf] = self::chain(false);

        self::assertBothEvaluators([$leaf], [$root], true);
    }

    public function testAcceptsValidIntermediateChain(): void
    {
        [$root, $intermediate, $leaf] = self::chain();

        self::assertBothEvaluators(
            [$leaf, $intermediate],
            [$root],
            true
        );
    }

    public function testRejectsMissingAnchors(): void
    {
        [, , $leaf] = self::chain(false);

        self::assertBothEvaluators([$leaf], [], false);
    }

    public function testRejectsDifferentRoot(): void
    {
        [, $intermediate, $leaf] = self::chain();
        [$differentRoot] = self::chain(false);

        self::assertBothEvaluators(
            [$leaf, $intermediate],
            [$differentRoot],
            false
        );
    }

    public function testRejectsRootIncludedInUntrustedX5c(): void
    {
        [$root, $intermediate, $leaf] = self::chain();

        self::assertBothEvaluators(
            [$leaf, $intermediate, $root],
            [$root],
            false
        );
    }

    public function testRejectsSelfSignedSigningCertificate(): void
    {
        [$root] = self::chain(false);

        self::assertBothEvaluators([$root], [$root], false);
    }

    public function testRejectsMissingIntermediate(): void
    {
        [$root, , $leaf] = self::chain();

        self::assertBothEvaluators([$leaf], [$root], false);
    }

    public function testRejectsReversedChainOrder(): void
    {
        [$root, $intermediate, $leaf] = self::chain();

        self::assertBothEvaluators(
            [$intermediate, $leaf],
            [$root],
            false
        );
    }

    public function testRejectsDuplicateCertificates(): void
    {
        [$root, $intermediate, $leaf] = self::chain();

        self::assertBothEvaluators(
            [$leaf, $intermediate, $intermediate],
            [$root],
            false
        );
    }

    public function testRejectsInvalidAnchorPem(): void
    {
        [, , $leaf] = self::chain(false);

        self::assertBothEvaluators(
            [$leaf],
            ['not-a-certificate'],
            false
        );
    }

    public function testRejectsIntermediateWithoutCaCapabilities(): void
    {
        [$root, $intermediate, $leaf] = self::chain(
            true,
            'bad_ca_ext'
        );

        self::assertBothEvaluators(
            [$leaf, $intermediate],
            [$root],
            false
        );
    }

    public function testRejectsLeafWithCaPrivileges(): void
    {
        [$root, $intermediate, $leaf] = self::chain(
            true,
            'intermediate_ext',
            'ca_ext'
        );

        self::assertBothEvaluators(
            [$leaf, $intermediate],
            [$root],
            false
        );
    }

    /**
     * @param list<string> $chainPems
     * @param list<string> $anchorPems
     */
    private static function assertBothEvaluators(
        array $chainPems,
        array $anchorPems,
        bool $expected
    ): void {
        $header = [
            'typ' => 'key-attestation+jwt',
            'alg' => 'ES256',
            'x5c' => array_map(self::pemBase64(...), $chainPems),
        ];

        $claims = [
            'iat' => time(),
            'attested_keys' => [['kty' => 'EC']],
        ];

        $token = self::b64(
            (string) json_encode($header, JSON_THROW_ON_ERROR)
        ) . '.' . self::b64(
            (string) json_encode($claims, JSON_THROW_ON_ERROR)
        ) . '.' . self::b64(str_repeat("\x01", 64));

        $legacyEvaluator = new LegacyEvaluator();
        $adapterEvaluator = new AdapterEvaluator();

        $evidences = [
            LegacyJwt::parse($token),
            AdapterJwt::parse($token),
        ];

        foreach ($evidences as $evidence) {
            $legacyResult = $legacyEvaluator
                ->chainMatchesTrustedAnchors($evidence, $anchorPems);

            $adapterResult = $adapterEvaluator
                ->chainMatchesTrustedAnchors($evidence, $anchorPems);

            self::assertSame($legacyResult, $adapterResult);
            self::assertSame($expected, $adapterResult);
        }
    }

    /** @return array{string, string, string} */
    private static function chain(
        bool $withIntermediate = true,
        string $intermediateExtension = 'intermediate_ext',
        string $leafExtension = 'leaf_ext'
    ): array {
        [$rootKey, $rootCert, $rootPem] = self::issue(
            'SIF HAIP TEST ROOT',
            null,
            null,
            'ca_ext'
        );

        if (!$withIntermediate) {
            [, , $leafPem] = self::issue(
                'SIF HAIP TEST LEAF',
                $rootCert,
                $rootKey,
                $leafExtension
            );

            return [$rootPem, '', $leafPem];
        }

        [$intermediateKey, $intermediateCert, $intermediatePem] = self::issue(
            'SIF HAIP TEST INTERMEDIATE',
            $rootCert,
            $rootKey,
            $intermediateExtension
        );

        [, , $leafPem] = self::issue(
            'SIF HAIP TEST LEAF',
            $intermediateCert,
            $intermediateKey,
            $leafExtension
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
            self::fail('Could not generate test private key.');
        }

        $csr = @openssl_csr_new(
            ['commonName' => $name],
            $privateKey,
            [
                'config' => $config,
                'digest_alg' => 'sha256',
            ]
        );

        if (!$csr instanceof OpenSSLCertificateSigningRequest) {
            self::fail('Could not generate test CSR.');
        }

        $certificate = @openssl_csr_sign(
            $csr,
            $issuer,
            $issuerPrivateKey ?? $privateKey,
            2,
            [
                'config' => $config,
                'digest_alg' => 'sha256',
                'x509_extensions' => $extension,
            ]
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
        $result = preg_replace(
            '/-----BEGIN CERTIFICATE-----|-----END CERTIFICATE-----|\s+/',
            '',
            $pem
        );

        if (!is_string($result)) {
            self::fail('Could not encode test certificate.');
        }

        return $result;
    }

    private static function b64(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
