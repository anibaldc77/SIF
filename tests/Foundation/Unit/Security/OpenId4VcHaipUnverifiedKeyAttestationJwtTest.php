<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipUnverifiedKeyAttestationJwt;

final class OpenId4VcHaipUnverifiedKeyAttestationJwtTest extends TestCase
{
    public function testParsesOnlyUnverifiedFieldsAndRetainsSigningInput(): void
    {
        $jwt = self::token();
        $parsed = OpenId4VcHaipUnverifiedKeyAttestationJwt::parse($jwt);
        $parts = explode('.', $jwt);

        self::assertSame($parts[0] . '.' . $parts[1], $parsed->signingInput());
        self::assertSame(str_repeat("\x01", 64), $parsed->rawSignature());
        self::assertSame('ES256', $parsed->unverifiedHeader()['alg']);
        self::assertSame(1710000000, $parsed->unverifiedClaims()['iat']);
        self::assertSame(['CERTIFICATE_DER'], $parsed->unverifiedCertificateDerChain());
    }

    public function testRejectsNoneAlgorithm(): void
    {
        self::assertRejected(self::token(['alg' => 'none']));
    }

    public function testRejectsSymmetricAlgorithm(): void
    {
        self::assertRejected(self::token(['alg' => 'HS256']));
    }

    public function testRejectsUnexpectedJoseType(): void
    {
        self::assertRejected(self::token(['typ' => 'JWT']));
    }

    public function testRejectsMissingX5c(): void
    {
        self::assertRejected(self::token(['x5c' => null]));
    }

    public function testRejectsIncorrectCertificateEncoding(): void
    {
        self::assertRejected(self::token(['x5c' => ['***']]));
    }

    public function testRejectsWrongSignatureLength(): void
    {
        self::assertRejected(self::token([], [], str_repeat("\x01", 63)));
    }

    public function testRejectsMissingIssuedAt(): void
    {
        self::assertRejected(self::token([], ['iat' => null]));
    }

    public function testRejectsNonIntegerExpiration(): void
    {
        self::assertRejected(self::token([], ['exp' => '1710000030']));
    }

    public function testRejectsMissingAttestedKeys(): void
    {
        self::assertRejected(self::token([], ['attested_keys' => []]));
    }

    public function testRejectsMalformedJwk(): void
    {
        self::assertRejected(self::token([], ['attested_keys' => [['crv' => 'P-256']]]));
    }

    public function testRejectsCriticalExtensions(): void
    {
        self::assertRejected(self::token(['crit' => ['exp']]));
    }

    public function testRejectsUnencodedPayloadHeader(): void
    {
        self::assertRejected(self::token(['b64' => false]));
    }

    public function testRejectsRemoteKeyUrls(): void
    {
        self::assertRejected(self::token(['jku' => 'https://wallet.example/jwks']));
    }

    public function testRejectsMalformedCompactSerialization(): void
    {
        self::assertRejected('header.payload');
    }

    public function testRejectsNonCanonicalBase64url(): void
    {
        $parts = explode('.', self::token());
        $parts[2] .= '=';
        self::assertRejected(implode('.', $parts));
    }

    public function testRejectsNonObjectClaims(): void
    {
        $parts = explode('.', self::token());
        $parts[1] = self::b64('[]');
        self::assertRejected(implode('.', $parts));
    }

    private static function assertRejected(string $jwt): void
    {
        try {
            OpenId4VcHaipUnverifiedKeyAttestationJwt::parse($jwt);
            self::fail('Untrusted key attestation must be rejected.');
        } catch (InvalidArgumentException) {
            self::assertTrue(true);
        }
    }

    /**
     * @param array<string, mixed> $headerOverrides
     * @param array<string, mixed> $claimsOverrides
     */
    private static function token(
        array $headerOverrides = [],
        array $claimsOverrides = [],
        string $rawSignature = ''
    ): string {
        $header = array_replace([
            'typ' => 'key-attestation+jwt',
            'alg' => 'ES256',
            'x5c' => [base64_encode('CERTIFICATE_DER')],
        ], $headerOverrides);
        $claims = array_replace([
            'iat' => 1710000000,
            'attested_keys' => [['kty' => 'EC', 'crv' => 'P-256', 'x' => 'x', 'y' => 'y']],
        ], $claimsOverrides);

        return self::b64((string) json_encode($header, JSON_THROW_ON_ERROR))
            . '.' . self::b64((string) json_encode($claims, JSON_THROW_ON_ERROR))
            . '.' . self::b64($rawSignature === '' ? str_repeat("\x01", 64) : $rawSignature);
    }

    private static function b64(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
