<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use OpenSSLCertificateSigningRequest;
use PHPUnit\Framework\TestCase;
use Sif\Adapters\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipUntrustedEs256SignatureMatcher as AdapterMatcher;
use Sif\Adapters\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipUnverifiedKeyAttestationJwt as AdapterJwt;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipUntrustedEs256SignatureMatcher as LegacyMatcher;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipUnverifiedKeyAttestationJwt as LegacyJwt;

final class OpenId4VcHaipAdapterEs256SignatureMatcherParityTest extends TestCase
{
    public function testSourcesDifferOnlyByNamespace(): void
    {
        $root = dirname(__DIR__, 4);

        $legacy = file_get_contents(
            $root . '/src/Foundation/Security/VerifiableCredentials/Haip/Issuance/OpenId4VcHaipUntrustedEs256SignatureMatcher.php'
        );

        $adapter = file_get_contents(
            $root . '/src/Adapters/Security/VerifiableCredentials/Haip/Issuance/OpenId4VcHaipUntrustedEs256SignatureMatcher.php'
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

    public function testBothMatchersAcceptAuthenticP256SignatureWithoutEstablishingTrust(): void
    {
        self::assertBothMatchers(self::signedToken(), true);
    }

    public function testBothMatchersRejectTamperedSignature(): void
    {
        $parts = explode('.', self::signedToken());

        $signature = self::decode($parts[2]);
        $signature[0] = chr(ord($signature[0]) ^ 1);

        $parts[2] = self::b64($signature);

        self::assertBothMatchers(implode('.', $parts), false);
    }

    public function testBothMatchersRejectTamperedHeaderAndClaims(): void
    {
        $parts = explode('.', self::signedToken());

        $header = json_decode(
            self::decode($parts[0]),
            true,
            16,
            JSON_THROW_ON_ERROR
        );

        self::assertIsArray($header);

        $header['kid'] = 'tampered';

        $changedHeader = $parts;
        $changedHeader[0] = self::b64(
            (string) json_encode($header, JSON_THROW_ON_ERROR)
        );

        self::assertBothMatchers(implode('.', $changedHeader), false);

        $claims = json_decode(
            self::decode($parts[1]),
            true,
            16,
            JSON_THROW_ON_ERROR
        );

        self::assertIsArray($claims);

        $claims['nonce'] = 'tampered';

        $changedClaims = $parts;
        $changedClaims[1] = self::b64(
            (string) json_encode($claims, JSON_THROW_ON_ERROR)
        );

        self::assertBothMatchers(implode('.', $changedClaims), false);
    }

    public function testBothMatchersRejectMalformedLeafCertificate(): void
    {
        $parts = explode('.', self::signedToken());

        $header = json_decode(
            self::decode($parts[0]),
            true,
            16,
            JSON_THROW_ON_ERROR
        );

        self::assertIsArray($header);

        $header['x5c'] = [base64_encode('not a certificate')];

        $parts[0] = self::b64(
            (string) json_encode($header, JSON_THROW_ON_ERROR)
        );

        self::assertBothMatchers(implode('.', $parts), false);
    }

    private static function assertBothMatchers(
        string $token,
        bool $expected
    ): void {
        $legacyMatcher = new LegacyMatcher();
        $adapterMatcher = new AdapterMatcher();

        $evidence = [
            LegacyJwt::parse($token),
            AdapterJwt::parse($token),
        ];

        foreach ($evidence as $jwt) {
            $legacyResult = $legacyMatcher->matches($jwt);
            $adapterResult = $adapterMatcher->matches($jwt);

            self::assertSame($legacyResult, $adapterResult);
            self::assertSame($expected, $adapterResult);
        }
    }

    private static function signedToken(): string
    {
        $configPath = __DIR__ . '/fixtures/haip-openssl-test.cnf';

        $privateKey = openssl_pkey_new([
            'private_key_type' => OPENSSL_KEYTYPE_EC,
            'curve_name' => 'prime256v1',
            'config' => $configPath,
        ]);

        self::assertNotFalse($privateKey);

        $csr = openssl_csr_new(
            ['commonName' => 'UNTRUSTED TEST ONLY'],
            $privateKey,
            [
                'digest_alg' => 'sha256',
                'config' => $configPath,
            ]
        );

        if (!$csr instanceof OpenSSLCertificateSigningRequest) {
            self::fail('Unable to create test CSR.');
        }

        $certificate = openssl_csr_sign(
            $csr,
            null,
            $privateKey,
            1,
            [
                'digest_alg' => 'sha256',
                'config' => $configPath,
            ]
        );

        self::assertNotFalse($certificate);
        self::assertTrue(openssl_x509_export($certificate, $pem));
        self::assertIsString($pem);

        $base64 = preg_replace(
            '/-----BEGIN CERTIFICATE-----|-----END CERTIFICATE-----|\s+/',
            '',
            $pem
        );

        self::assertIsString($base64);

        $header = [
            'alg' => 'ES256',
            'typ' => 'key-attestation+jwt',
            'x5c' => [$base64],
        ];

        $claims = [
            'iat' => time(),
            'attested_keys' => [['kty' => 'EC']],
        ];

        $signingInput = self::b64(
            (string) json_encode($header, JSON_THROW_ON_ERROR)
        ) . '.' . self::b64(
            (string) json_encode($claims, JSON_THROW_ON_ERROR)
        );

        self::assertTrue(
            openssl_sign(
                $signingInput,
                $derSignature,
                $privateKey,
                OPENSSL_ALGO_SHA256
            )
        );

        return $signingInput . '.' . self::b64(
            self::derToJose($derSignature)
        );
    }

    private static function derToJose(string $der): string
    {
        $offset = 0;

        self::assertSame(0x30, ord($der[$offset++]));

        $sequenceLength = ord($der[$offset++]);

        if (($sequenceLength & 0x80) !== 0) {
            $octets = $sequenceLength & 0x7f;
            $sequenceLength = 0;

            for ($i = 0; $i < $octets; $i++) {
                $sequenceLength = ($sequenceLength << 8)
                    | ord($der[$offset++]);
            }
        }

        self::assertSame(
            strlen($der) - $offset,
            $sequenceLength
        );

        $components = [];

        for ($i = 0; $i < 2; $i++) {
            self::assertSame(0x02, ord($der[$offset++]));

            $length = ord($der[$offset++]);
            $value = ltrim(
                substr($der, $offset, $length),
                "\x00"
            );

            $offset += $length;

            self::assertLessThanOrEqual(32, strlen($value));

            $components[] = str_pad(
                $value,
                32,
                "\x00",
                STR_PAD_LEFT
            );
        }

        self::assertSame(strlen($der), $offset);

        return implode('', $components);
    }

    private static function b64(string $value): string
    {
        return rtrim(
            strtr(base64_encode($value), '+/', '-_'),
            '='
        );
    }

    private static function decode(string $value): string
    {
        $decoded = base64_decode(
            strtr($value, '-_', '+/'),
            true
        );

        self::assertNotFalse($decoded);

        return $decoded;
    }
}
