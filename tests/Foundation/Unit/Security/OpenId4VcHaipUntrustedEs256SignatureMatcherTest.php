<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipUntrustedEs256SignatureMatcher;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipUnverifiedKeyAttestationJwt;

final class OpenId4VcHaipUntrustedEs256SignatureMatcherTest extends TestCase
{
    public function testMatchesAuthenticP256SignatureWithoutGrantingTrust(): void
    {
        [$jwt] = self::token();
        self::assertTrue(self::signatureMatches($jwt));
    }

    public function testRejectsModifiedSignature(): void
    {
        [$jwt] = self::token();
        $parts = explode('.', $jwt);
        $signature = self::decode($parts[2]);
        $signature[30] = chr(ord($signature[30]) ^ 1);
        $parts[2] = self::b64($signature);
        self::assertFalse(self::signatureMatches(implode('.', $parts)));
    }

    public function testRejectsModifiedClaims(): void
    {
        [$jwt] = self::token();
        $parts = explode('.', $jwt);
        $claims = json_decode(self::decode($parts[1]), true, 16, JSON_THROW_ON_ERROR);
        $claims['nonce'] = 'tampered';
        $parts[1] = self::b64((string) json_encode($claims, JSON_THROW_ON_ERROR));
        self::assertFalse(self::signatureMatches(implode('.', $parts)));
    }

    public function testRejectsModifiedHeader(): void
    {
        [$jwt] = self::token();
        $parts = explode('.', $jwt);
        $header = json_decode(self::decode($parts[0]), true, 16, JSON_THROW_ON_ERROR);
        $header['kid'] = 'altered';
        $parts[0] = self::b64((string) json_encode($header, JSON_THROW_ON_ERROR));
        self::assertFalse(self::signatureMatches(implode('.', $parts)));
    }

    public function testRejectsDifferentSigningCertificate(): void
    {
        [$jwt] = self::token();
        [$differentJwt] = self::token();
        $parts = explode('.', $jwt);
        $otherParts = explode('.', $differentJwt);
        $header = json_decode(self::decode($parts[0]), true, 16, JSON_THROW_ON_ERROR);
        $otherHeader = json_decode(self::decode($otherParts[0]), true, 16, JSON_THROW_ON_ERROR);
        $header['x5c'] = $otherHeader['x5c'];
        $parts[0] = self::b64((string) json_encode($header, JSON_THROW_ON_ERROR));
        self::assertFalse(self::signatureMatches(implode('.', $parts)));
    }

    public function testRejectsMalformedLeafCertificate(): void
    {
        [$jwt] = self::token();
        $parts = explode('.', $jwt);
        $header = json_decode(self::decode($parts[0]), true, 16, JSON_THROW_ON_ERROR);
        $header['x5c'] = [base64_encode('not a certificate')];
        $parts[0] = self::b64((string) json_encode($header, JSON_THROW_ON_ERROR));
        self::assertFalse(self::signatureMatches(implode('.', $parts)));
    }

    public function testRejectsP384CertificateWithWellFormedJwsSignature(): void
    {
        [$jwt] = self::token('secp384r1');
        self::assertFalse(self::signatureMatches($jwt));
    }

    public function testRejectsRsaLeaf(): void
    {
        [$jwt] = self::token('RSA');
        self::assertFalse(self::signatureMatches($jwt));
    }

    public function testSelfSignedSignatureMatchDoesNotConstituteTrust(): void
    {
        [$jwt] = self::token();
        self::assertTrue(self::signatureMatches($jwt));
    }

    /** @return array{string, string} */
    private static function token(string $curve = 'prime256v1', int $days = 1): array
    {
        $config = $curve === 'RSA'
            ? ['private_key_type' => OPENSSL_KEYTYPE_RSA, 'private_key_bits' => 2048]
            : ['private_key_type' => OPENSSL_KEYTYPE_EC, 'curve_name' => $curve];
        $configPath = __DIR__ . '/fixtures/haip-openssl-test.cnf';
        $config['config'] = $configPath;
        $privateKey = openssl_pkey_new($config);
        self::assertNotFalse($privateKey);
        $csr = openssl_csr_new(['commonName' => 'UNTRUSTED TEST ONLY'], $privateKey, ['digest_alg' => 'sha256', 'config' => $configPath]);
        if (!$csr instanceof \OpenSSLCertificateSigningRequest) {
            self::fail('Unable to create test CSR.');
        }
        $certificate = openssl_csr_sign($csr, null, $privateKey, $days, ['digest_alg' => 'sha256', 'config' => $configPath]);
        self::assertNotFalse($certificate);
        self::assertTrue(openssl_x509_export($certificate, $pem));
        $base64 = preg_replace('/-----BEGIN CERTIFICATE-----|-----END CERTIFICATE-----|\s+/', '', $pem);
        self::assertIsString($base64);
        $header = ['alg' => 'ES256', 'typ' => 'key-attestation+jwt', 'x5c' => [$base64]];
        $claims = ['iat' => time(), 'attested_keys' => [['kty' => 'EC']]];
        $signingInput = self::b64((string) json_encode($header, JSON_THROW_ON_ERROR))
            . '.' . self::b64((string) json_encode($claims, JSON_THROW_ON_ERROR));
        self::assertTrue(openssl_sign($signingInput, $der, $privateKey, OPENSSL_ALGO_SHA256));
        $raw = match ($curve) {
            'RSA' => str_repeat("\x01", 64),
            'secp384r1' => substr(self::derToJose($der, 48), 0, 64),
            default => self::derToJose($der, 32),
        };
        return [$signingInput . '.' . self::b64($raw), $pem];
    }

    private static function signatureMatches(string $jwt): bool
    {
        return (new OpenId4VcHaipUntrustedEs256SignatureMatcher())->matches(
            OpenId4VcHaipUnverifiedKeyAttestationJwt::parse($jwt)
        );
    }

    private static function derToJose(string $der, int $componentWidth): string
    {
        // OpenSSL produces short-form DER for the test curves (P-256, P-384).
        $offset = 0;
        self::assertSame(0x30, ord($der[$offset++]));
        $sequenceLength = ord($der[$offset++]);
        if ($sequenceLength & 0x80) {
            $lengthOctets = $sequenceLength & 0x7f;
            $sequenceLength = 0;
            for ($i = 0; $i < $lengthOctets; $i++) {
                $sequenceLength = ($sequenceLength << 8) | ord($der[$offset++]);
            }
        }
        self::assertSame(strlen($der) - $offset, $sequenceLength);
        $components = [];
        for ($i = 0; $i < 2; $i++) {
            self::assertSame(0x02, ord($der[$offset++]));
            $len = ord($der[$offset++]);
            $value = ltrim(substr($der, $offset, $len), "\x00");
            $offset += $len;
            self::assertLessThanOrEqual($componentWidth, strlen($value));
            $components[] = str_pad($value, $componentWidth, "\x00", STR_PAD_LEFT);
        }
        self::assertSame(strlen($der), $offset);
        return implode('', $components);
    }

    private static function b64(string $text): string
    {
        return rtrim(strtr(base64_encode($text), '+/', '-_'), '=');
    }

    private static function decode(string $text): string
    {
        $value = base64_decode(strtr($text, '-_', '+/'), true);
        self::assertNotFalse($value);
        return $value;
    }
}
