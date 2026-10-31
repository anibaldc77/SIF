<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationEvidenceIdentity;

final class OpenId4VcHaipKeyAttestationEvidenceIdentityTest extends TestCase
{
    public function testValidSha256FingerprintIsAccepted(): void
    {
        $fingerprint = str_repeat('a', 64);

        $identity = new OpenId4VcHaipKeyAttestationEvidenceIdentity(
            $fingerprint
        );

        self::assertSame('sha-256', $identity->algorithm());
        self::assertSame($fingerprint, $identity->sha256Fingerprint());
    }

    public function testIdenticalFingerprintsMatch(): void
    {
        $first = new OpenId4VcHaipKeyAttestationEvidenceIdentity(
            str_repeat('a', 64)
        );

        $second = new OpenId4VcHaipKeyAttestationEvidenceIdentity(
            str_repeat('a', 64)
        );

        self::assertTrue($first->matches($second));
    }

    public function testDifferentFingerprintsDoNotMatch(): void
    {
        $first = new OpenId4VcHaipKeyAttestationEvidenceIdentity(
            str_repeat('a', 64)
        );

        $second = new OpenId4VcHaipKeyAttestationEvidenceIdentity(
            str_repeat('b', 64)
        );

        self::assertFalse($first->matches($second));
    }

    public function testInvalidFingerprintLengthIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OpenId4VcHaipKeyAttestationEvidenceIdentity(
            str_repeat('a', 63)
        );
    }

    public function testUppercaseFingerprintIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OpenId4VcHaipKeyAttestationEvidenceIdentity(
            str_repeat('A', 64)
        );
    }

    public function testNonHexadecimalFingerprintIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OpenId4VcHaipKeyAttestationEvidenceIdentity(
            str_repeat('g', 64)
        );
    }
}