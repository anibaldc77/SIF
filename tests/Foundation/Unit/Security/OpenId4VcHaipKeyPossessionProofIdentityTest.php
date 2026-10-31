<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyPossessionProofIdentity;
use Sif\Foundation\Security\VerifiableCredentials\Issuance\CredentialIssuanceProof;

final class OpenId4VcHaipKeyPossessionProofIdentityTest extends TestCase
{
    public function testValidSha256FingerprintIsAccepted(): void
    {
        $fingerprint = str_repeat('a', 64);

        $identity = new OpenId4VcHaipKeyPossessionProofIdentity(
            $fingerprint
        );

        self::assertSame('sha-256', $identity->algorithm());
        self::assertSame(
            $fingerprint,
            $identity->sha256Fingerprint()
        );
    }

    public function testIdenticalFingerprintsMatch(): void
    {
        $first = new OpenId4VcHaipKeyPossessionProofIdentity(
            str_repeat('a', 64)
        );

        $second = new OpenId4VcHaipKeyPossessionProofIdentity(
            str_repeat('a', 64)
        );

        self::assertTrue($first->matches($second));
    }

    public function testDifferentFingerprintsDoNotMatch(): void
    {
        $first = new OpenId4VcHaipKeyPossessionProofIdentity(
            str_repeat('a', 64)
        );

        $second = new OpenId4VcHaipKeyPossessionProofIdentity(
            str_repeat('b', 64)
        );

        self::assertFalse($first->matches($second));
    }

    public function testEmptyFingerprintIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OpenId4VcHaipKeyPossessionProofIdentity('');
    }

    public function testShortFingerprintIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OpenId4VcHaipKeyPossessionProofIdentity(
            str_repeat('a', 63)
        );
    }

    public function testUppercaseFingerprintIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OpenId4VcHaipKeyPossessionProofIdentity(
            str_repeat('A', 64)
        );
    }

    public function testNonHexadecimalFingerprintIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OpenId4VcHaipKeyPossessionProofIdentity(
            str_repeat('g', 64)
        );
    }

    public function testFingerprintWithTrailingCharactersIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OpenId4VcHaipKeyPossessionProofIdentity(
            str_repeat('a', 64) . 'extra'
        );
    }

    public function testFactoryHashesExactSerializedProof(): void
    {
        $serializedProof = 'header.payload.signature';

        $proof = new CredentialIssuanceProof(
            'jwt',
            $serializedProof
        );

        $identity = OpenId4VcHaipKeyPossessionProofIdentity::fromIssuanceProof(
            $proof
        );

        self::assertSame(
            hash('sha256', $serializedProof),
            $identity->sha256Fingerprint()
        );
    }

    public function testDifferentSerializedProofsHaveDifferentIdentities(): void
    {
        $first = OpenId4VcHaipKeyPossessionProofIdentity::fromIssuanceProof(
            new CredentialIssuanceProof(
                'jwt',
                'header.payload.signature-a'
            )
        );

        $second = OpenId4VcHaipKeyPossessionProofIdentity::fromIssuanceProof(
            new CredentialIssuanceProof(
                'jwt',
                'header.payload.signature-b'
            )
        );

        self::assertFalse($first->matches($second));
    }

    public function testProofIdentityDependsOnSerializedBytesNotProofType(): void
    {
        $first = OpenId4VcHaipKeyPossessionProofIdentity::fromIssuanceProof(
            new CredentialIssuanceProof(
                'jwt',
                'same-serialized-proof'
            )
        );

        $second = OpenId4VcHaipKeyPossessionProofIdentity::fromIssuanceProof(
            new CredentialIssuanceProof(
                'attestation',
                'same-serialized-proof'
            )
        );

        self::assertTrue($first->matches($second));
    }
}