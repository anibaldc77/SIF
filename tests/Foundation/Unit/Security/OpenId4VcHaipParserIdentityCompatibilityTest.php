<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use PHPUnit\Framework\TestCase;
use ReflectionClass;
use Sif\Adapters\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipUnverifiedKeyAttestationJwt as AdapterJwt;
use Sif\Foundation\Security\Contracts\OpenId4VcHaipUnverifiedKeyAttestationEvidenceInterface;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipUnverifiedKeyAttestationJwt as LegacyJwt;

final class OpenId4VcHaipParserIdentityCompatibilityTest extends TestCase
{
    public function testLegacyParserPreservesCanonicalPublicIdentity(): void
    {
        $evidence = LegacyJwt::parse(self::token());

        self::assertSame(LegacyJwt::class, get_class($evidence));
        self::assertSame(
            LegacyJwt::class,
            (new ReflectionClass(LegacyJwt::class))->getName()
        );

        self::assertStringStartsWith(
            'O:' . strlen(LegacyJwt::class) . ':"' . LegacyJwt::class . '":',
            serialize($evidence)
        );

        self::assertInstanceOf(
            OpenId4VcHaipUnverifiedKeyAttestationEvidenceInterface::class,
            $evidence
        );
    }

    public function testAdapterPreservesDistinctRuntimeIdentity(): void
    {
        $evidence = AdapterJwt::parse(self::token());

        self::assertNotSame(LegacyJwt::class, AdapterJwt::class);
        self::assertSame(AdapterJwt::class, get_class($evidence));

        self::assertSame(
            AdapterJwt::class,
            (new ReflectionClass(AdapterJwt::class))->getName()
        );

        self::assertStringStartsWith(
            'O:' . strlen(AdapterJwt::class) . ':"' . AdapterJwt::class . '":',
            serialize($evidence)
        );

        self::assertInstanceOf(
            OpenId4VcHaipUnverifiedKeyAttestationEvidenceInterface::class,
            $evidence
        );

        self::assertTrue(
            (new ReflectionClass(LegacyJwt::class))->isReadOnly()
        );

        self::assertTrue(
            (new ReflectionClass(AdapterJwt::class))->isReadOnly()
        );
    }

    private static function token(): string
    {
        $header = [
            'typ' => 'key-attestation+jwt',
            'alg' => 'ES256',
            'x5c' => [base64_encode('TEST_ONLY_CERTIFICATE_DER')],
        ];

        $claims = [
            'iat' => 1710000000,
            'attested_keys' => [['kty' => 'EC']],
        ];

        return self::b64(
            (string) json_encode($header, JSON_THROW_ON_ERROR)
        ) . '.' . self::b64(
            (string) json_encode($claims, JSON_THROW_ON_ERROR)
        ) . '.' . self::b64(str_repeat("\x01", 64));
    }

    private static function b64(string $value): string
    {
        return rtrim(strtr(base64_encode($value), '+/', '-_'), '=');
    }
}
