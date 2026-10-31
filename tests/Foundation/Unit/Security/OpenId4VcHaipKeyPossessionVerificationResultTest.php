<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyPossessionProofIdentity;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyPossessionVerificationResult;

final class OpenId4VcHaipKeyPossessionVerificationResultTest extends TestCase
{
    public function testValidPossessionCoversVerifiedKeys(): void
    {
        $result = new OpenId4VcHaipKeyPossessionVerificationResult(
            true,
            ['key-1', 'key-2']
        );

        self::assertTrue($result->valid());
        self::assertSame(
            ['key-1', 'key-2'],
            $result->verifiedKeyReferences()
        );
        self::assertTrue($result->coversKey('key-1'));
        self::assertTrue($result->coversAllKeys(['key-1', 'key-2']));
        self::assertSame([], $result->violations());
    }

    public function testUnverifiedKeyIsNotCovered(): void
    {
        $result = new OpenId4VcHaipKeyPossessionVerificationResult(
            true,
            ['key-1']
        );

        self::assertFalse($result->coversKey('key-2'));
        self::assertFalse(
            $result->coversAllKeys(['key-1', 'key-2'])
        );
    }

    public function testInvalidPossessionDoesNotCoverKeys(): void
    {
        $result = new OpenId4VcHaipKeyPossessionVerificationResult(
            false,
            [],
            ['signature_invalid']
        );

        self::assertFalse($result->valid());
        self::assertFalse($result->coversKey('key-1'));
        self::assertFalse($result->coversAllKeys(['key-1']));
        self::assertSame(['signature_invalid'], $result->violations());
    }

    public function testValidPossessionRequiresVerifiedKeys(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OpenId4VcHaipKeyPossessionVerificationResult(
            true,
            []
        );
    }

    public function testInvalidPossessionCannotExposeVerifiedKeys(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OpenId4VcHaipKeyPossessionVerificationResult(
            false,
            ['key-1']
        );
    }

    public function testValidPossessionCannotContainViolations(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OpenId4VcHaipKeyPossessionVerificationResult(
            true,
            ['key-1'],
            ['signature_invalid']
        );
    }

    public function testEmptyRequiredKeyListIsNotAccepted(): void
    {
        $result = new OpenId4VcHaipKeyPossessionVerificationResult(
            true,
            ['key-1']
        );

        self::assertFalse($result->coversAllKeys([]));
    }

    public function testBlankVerifiedKeyReferenceIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OpenId4VcHaipKeyPossessionVerificationResult(
            true,
            ['  ']
        );
    }

    public function testBlankViolationIsRejected(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OpenId4VcHaipKeyPossessionVerificationResult(
            false,
            [],
            ['  ']
        );
    }

    public function testVerifiedIssuerAndProofIdentityArePreserved(): void
    {
        $identity = new OpenId4VcHaipKeyPossessionProofIdentity(
            str_repeat('a', 64)
        );

        $result = new OpenId4VcHaipKeyPossessionVerificationResult(
            true,
            ['key-1'],
            [],
            'https://issuer.example',
            $identity
        );

        self::assertTrue($result->valid());
        self::assertSame(
            'https://issuer.example',
            $result->verifiedIssuerIdentifier()
        );
        self::assertSame(
            $identity,
            $result->verifiedProofIdentity()
        );
    }

    public function testVerificationContextAttributesAreOptional(): void
    {
        $result = new OpenId4VcHaipKeyPossessionVerificationResult(
            true,
            ['key-1']
        );

        self::assertNull($result->verifiedIssuerIdentifier());
        self::assertNull($result->verifiedProofIdentity());
    }
}