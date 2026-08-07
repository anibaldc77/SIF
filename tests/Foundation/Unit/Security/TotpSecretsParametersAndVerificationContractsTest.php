<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Unit\Security;

use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\TotpSecretGeneratorInterface;
use Sif\Foundation\Security\Contracts\TotpVerifierInterface;
use Sif\Foundation\Security\Exceptions\InvalidTotpConfigurationException;
use Sif\Foundation\Security\MultiFactor\Totp\TotpCode;
use Sif\Foundation\Security\MultiFactor\Totp\TotpHashAlgorithm;
use Sif\Foundation\Security\MultiFactor\Totp\TotpParameters;
use Sif\Foundation\Security\MultiFactor\Totp\TotpSecret;
use Sif\Foundation\Security\MultiFactor\Totp\TotpVerificationResult;

final class TotpSecretsParametersAndVerificationContractsTest extends TestCase
{
    public function testSecretIsNormalizedAndNeverRendered(): void
    {
        $secret = new TotpSecret('jbsw-y3dp ehpk-3pxp');

        self::assertSame(16, $secret->encodedLength());
        self::assertSame('JBSWY3DPEHPK3PXP', $secret->expose(static fn (string $value): string => $value));
        self::assertSame('[REDACTED]', (string) $secret);
        self::assertSame('[REDACTED]', $secret->__debugInfo()['value']);
    }

    public function testCodesAreSensitiveAndStructurallyValidated(): void
    {
        $code = new TotpCode('012345');

        self::assertSame(6, $code->digits());
        self::assertSame('012345', $code->expose(static fn (string $value): string => $value));
        self::assertSame('[REDACTED]', (string) $code);

        $this->expectException(InvalidTotpConfigurationException::class);
        new TotpCode('12A456');
    }

    public function testParametersAreExplicitAndDeterministic(): void
    {
        $parameters = new TotpParameters(TotpHashAlgorithm::sha256(), 8, 60, 2, 0);

        self::assertSame([
            'algorithm' => 'sha256',
            'digits' => 8,
            'period_seconds' => 60,
            'allowed_past_windows' => 2,
            'allowed_future_windows' => 0,
        ], $parameters->snapshot());
        self::assertTrue(TotpHashAlgorithm::sha256()->equals($parameters->algorithm()));
    }

    public function testVerificationResultsDoNotExposeCodesOrSecrets(): void
    {
        $verified = TotpVerificationResult::verified(123456);
        $rejected = TotpVerificationResult::rejected();

        self::assertTrue($verified->isVerified());
        self::assertSame(123456, $verified->matchedCounter());
        self::assertFalse($rejected->isVerified());
        self::assertNull($rejected->matchedCounter());
    }

    public function testContractsRemainCryptographyAndPersistenceNeutral(): void
    {
        foreach ([TotpSecretGeneratorInterface::class, TotpVerifierInterface::class] as $contract) {
            $reflection = new \ReflectionClass($contract);
            $source = file_get_contents((string) $reflection->getFileName());

            self::assertIsString($source);
            self::assertStringNotContainsString('BaseModel', $source);
            self::assertStringNotContainsString('PDO', $source);
            self::assertStringNotContainsString('Redis', $source);
            self::assertStringNotContainsString('GoogleAuthenticator', $source);
        }
    }
}
