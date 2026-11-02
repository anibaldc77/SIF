<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sif\Adapters\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipUnverifiedKeyAttestationJwt as AdapterJwt;
use Sif\Foundation\Security\Contracts\OpenId4VcHaipUnverifiedKeyAttestationEvidenceInterface;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipExplicitX509TrustEvaluator;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipUntrustedEs256SignatureMatcher;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipUnverifiedKeyAttestationJwt as LegacyJwt;

final class OpenId4VcHaipAdapterParserParityTest extends TestCase
{
    public function testTransitionalParserSourcesDifferOnlyByNamespace(): void
    {
        $root = dirname(__DIR__, 4);

        $legacy = file_get_contents(
            $root . '/src/Foundation/Security/VerifiableCredentials/Haip/Issuance/OpenId4VcHaipUnverifiedKeyAttestationJwt.php'
        );

        $adapter = file_get_contents(
            $root . '/src/Adapters/Security/VerifiableCredentials/Haip/Issuance/OpenId4VcHaipUnverifiedKeyAttestationJwt.php'
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

    public function testAdapterPreservesUnverifiedStructuralEvidence(): void
    {
        $token = self::token();

        $legacy = LegacyJwt::parse($token);
        $adapter = AdapterJwt::parse($token);

        self::assertInstanceOf(
            OpenId4VcHaipUnverifiedKeyAttestationEvidenceInterface::class,
            $adapter
        );

        self::assertSame($legacy->signingInput(), $adapter->signingInput());
        self::assertSame($legacy->rawSignature(), $adapter->rawSignature());
        self::assertSame($legacy->unverifiedHeader(), $adapter->unverifiedHeader());

        // Nested JSON objects remain stdClass in both representations.
        self::assertSame(
            json_encode($legacy->unverifiedClaims(), JSON_THROW_ON_ERROR),
            json_encode($adapter->unverifiedClaims(), JSON_THROW_ON_ERROR)
        );

        self::assertSame(
            $legacy->unverifiedCertificateDerChain(),
            $adapter->unverifiedCertificateDerChain()
        );
    }

    public function testBothParsersRejectTheSameInvalidEvidence(): void
    {
        $invalid = [
            'empty token' => '',
            'incomplete compact JWT' => 'header.payload',
            'none algorithm' => self::token(['alg' => 'none']),
            'symmetric algorithm' => self::token(['alg' => 'HS256']),
            'unexpected typ' => self::token(['typ' => 'JWT']),
            'critical extension' => self::token(['crit' => ['exp']]),
            'unencoded payload' => self::token(['b64' => false]),
            'remote JWK URL' => self::token(['jku' => 'https://wallet.example/jwks']),
            'remote X509 URL' => self::token(['x5u' => 'https://wallet.example/cert']),
            'embedded JWK' => self::token(['jwk' => ['kty' => 'EC']]),
            'missing x5c' => self::token(['x5c' => null]),
            'empty x5c' => self::token(['x5c' => []]),
            'invalid certificate base64' => self::token(['x5c' => ['***']]),
            'too many certificates' => self::token([
                'x5c' => array_fill(0, 9, base64_encode('DER')),
            ]),
            'missing iat' => self::token([], ['iat' => null]),
            'invalid exp' => self::token([], ['exp' => 'invalid']),
            'empty attested keys' => self::token([], ['attested_keys' => []]),
            'malformed JWK' => self::token([], [
                'attested_keys' => [['crv' => 'P-256']],
            ]),
            'invalid nonce' => self::token([], ['nonce' => '']),
            'invalid issuer' => self::token([], ['iss' => ' ']),
            'invalid signature length' => self::token([], [], str_repeat("\x01", 63)),
        ];

        foreach ($invalid as $reason => $token) {
            self::assertSame(
                self::legacyRejection($token),
                self::adapterRejection($token),
                $reason
            );
        }
    }

    public function testExistingCryptographicConsumersAcceptAdapterEvidenceWithoutTrust(): void
    {
        // Syntactically valid JWT, but the certificate is not real.
        $evidence = AdapterJwt::parse(self::token());

        self::assertFalse(
            (new OpenId4VcHaipUntrustedEs256SignatureMatcher())
                ->matches($evidence)
        );

        self::assertFalse(
            (new OpenId4VcHaipExplicitX509TrustEvaluator())
                ->chainMatchesTrustedAnchors(
                    $evidence,
                    ['not-a-trusted-anchor']
                )
        );
    }

    private static function legacyRejection(string $token): string
    {
        try {
            LegacyJwt::parse($token);
        } catch (InvalidArgumentException $exception) {
            return $exception->getMessage();
        }

        self::fail('Legacy parser unexpectedly accepted invalid evidence.');
    }

    private static function adapterRejection(string $token): string
    {
        try {
            AdapterJwt::parse($token);
        } catch (InvalidArgumentException $exception) {
            return $exception->getMessage();
        }

        self::fail('Adapter parser unexpectedly accepted invalid evidence.');
    }

    /**
     * @param array<string, mixed> $headerOverrides
     * @param array<string, mixed> $claimOverrides
     */
    private static function token(
        array $headerOverrides = [],
        array $claimOverrides = [],
        ?string $signature = null
    ): string {
        $header = array_replace([
            'typ' => 'key-attestation+jwt',
            'alg' => 'ES256',
            'x5c' => [base64_encode('TEST_ONLY_CERTIFICATE_DER')],
        ], $headerOverrides);

        $claims = array_replace([
            'iat' => 1710000000,
            'attested_keys' => [
                ['kty' => 'EC', 'crv' => 'P-256', 'x' => 'x', 'y' => 'y'],
            ],
        ], $claimOverrides);

        return self::b64(
            (string) json_encode($header, JSON_THROW_ON_ERROR)
        ) . '.' . self::b64(
            (string) json_encode($claims, JSON_THROW_ON_ERROR)
        ) . '.' . self::b64(
            $signature ?? str_repeat("\x01", 64)
        );
    }

    private static function b64(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
