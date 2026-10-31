<?php
declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\VerifiableCredentials\Haip\Issuance\OpenId4VcHaipKeyAttestationVerificationResult;

final class OpenId4VcHaipKeyAttestationVerificationResultTest extends TestCase
{
    public function testVerifiedAttestationCoversAllRequiredKeys(): void
    {
        $result = new OpenId4VcHaipKeyAttestationVerificationResult(
            true,
            ['key-1', 'key-2']
        );

        self::assertTrue($result->valid());
        self::assertTrue($result->coversKey('key-1'));
        self::assertTrue($result->coversAllKeys(['key-1', 'key-2']));
        self::assertFalse($result->coversAllKeys(['key-1', 'key-3']));
    }

    public function testInvalidAttestationDoesNotCoverKeys(): void
    {
        $result = new OpenId4VcHaipKeyAttestationVerificationResult(
            false,
            [],
            ['signature_invalid']
        );

        self::assertFalse($result->valid());
        self::assertFalse($result->coversKey('key-1'));
        self::assertFalse($result->coversAllKeys(['key-1']));
        self::assertSame(['signature_invalid'], $result->violations());
    }

    public function testValidAttestationRequiresVerifiedKeys(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OpenId4VcHaipKeyAttestationVerificationResult(
            true,
            []
        );
    }

    public function testInvalidAttestationCannotExposeVerifiedKeys(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OpenId4VcHaipKeyAttestationVerificationResult(
            false,
            ['key-1']
        );
    }

    public function testValidAttestationCannotContainViolations(): void
    {
        $this->expectException(InvalidArgumentException::class);

        new OpenId4VcHaipKeyAttestationVerificationResult(
            true,
            ['key-1'],
            ['invalid_signature']
        );
    }

    public function testEmptyRequiredKeyListIsNotAcceptedAsCoverage(): void
    {
        $result = new OpenId4VcHaipKeyAttestationVerificationResult(
            true,
            ['key-1']
        );

        self::assertFalse($result->coversAllKeys([]));
    }
}
