<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use Sif\Foundation\Security\Contracts\OpenId4VcHaipUnverifiedKeyAttestationEvidenceInterface;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipUnverifiedKeyAttestationJwt;

final class OpenId4VcHaipUnverifiedKeyAttestationEvidenceContractTest extends TestCase
{
    public function testExistingParserPreservesUnverifiedEvidenceContract(): void
    {
        $header = [
            'typ' => 'key-attestation+jwt',
            'alg' => 'ES256',
            'x5c' => [base64_encode('TEST_CERTIFICATE_DER')],
        ];

        $claims = [
            'iat' => 1710000000,
            'attested_keys' => [['kty' => 'EC']],
        ];

        $encodedHeader = self::b64(
            (string) json_encode($header, JSON_THROW_ON_ERROR)
        );

        $encodedClaims = self::b64(
            (string) json_encode($claims, JSON_THROW_ON_ERROR)
        );

        $signature = str_repeat("\x01", 64);

        $token = $encodedHeader
            . '.'
            . $encodedClaims
            . '.'
            . self::b64($signature);

        $evidence = OpenId4VcHaipUnverifiedKeyAttestationJwt::parse($token);

        self::assertInstanceOf(
            OpenId4VcHaipUnverifiedKeyAttestationEvidenceInterface::class,
            $evidence
        );

        self::assertSame(
            $encodedHeader . '.' . $encodedClaims,
            $evidence->signingInput()
        );

        self::assertSame($signature, $evidence->rawSignature());
        self::assertSame($header, $evidence->unverifiedHeader());
       $unverifiedClaims = $evidence->unverifiedClaims();

self::assertSame(
    1710000000,
    $unverifiedClaims['iat']
);

self::assertIsArray(
    $unverifiedClaims['attested_keys']
);

self::assertCount(
    1,
    $unverifiedClaims['attested_keys']
);

self::assertInstanceOf(
    \stdClass::class,
    $unverifiedClaims['attested_keys'][0]
);

self::assertSame(
    'EC',
    $unverifiedClaims['attested_keys'][0]->kty
);

        self::assertSame(
            ['TEST_CERTIFICATE_DER'],
            $evidence->unverifiedCertificateDerChain()
        );
    }

    public function testContractDoesNotExposeTrustDecisions(): void
    {
        $reflection = new ReflectionClass(
            OpenId4VcHaipUnverifiedKeyAttestationEvidenceInterface::class
        );

        self::assertTrue($reflection->isInterface());

        $methods = array_map(
            static fn (ReflectionMethod $method): string => $method->getName(),
            $reflection->getMethods()
        );

        sort($methods);

        self::assertSame(
            [
                'rawSignature',
                'signingInput',
                'unverifiedCertificateDerChain',
                'unverifiedClaims',
                'unverifiedHeader',
            ],
            $methods
        );

        self::assertFalse($reflection->hasMethod('valid'));
        self::assertFalse($reflection->hasMethod('verifiedIssuerIdentifier'));
        self::assertFalse($reflection->hasMethod('verifiedKeyReferences'));
    }

    private static function b64(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
