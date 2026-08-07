<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\MultiFactor\Totp\Base32Codec;
use Sif\Foundation\Security\MultiFactor\Totp\NativeTotpCodeGenerator;
use Sif\Foundation\Security\MultiFactor\Totp\NativeTotpSecretGenerator;
use Sif\Foundation\Security\MultiFactor\Totp\NativeTotpVerifier;
use Sif\Foundation\Security\MultiFactor\Totp\TotpCode;
use Sif\Foundation\Security\MultiFactor\Totp\TotpHashAlgorithm;
use Sif\Foundation\Security\MultiFactor\Totp\TotpParameters;
use Sif\Foundation\Security\MultiFactor\Totp\TotpSecret;

final class NativeTotpGenerationAndVerificationAdaptersTest extends TestCase
{
    private const RFC_SECRET = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';

    public function testBase32CodecRoundTripsBinaryMaterial(): void
    {
        $codec = new Base32Codec();
        $binary = "\x00\x01\x02native-totp\xff";

        self::assertSame($binary, $codec->decode($codec->encode($binary)));
    }

    public function testNativeGeneratorProducesASecretWithAtLeastOneHundredAndSixtyBits(): void
    {
        $secret = (new NativeTotpSecretGenerator())->generate();

        self::assertGreaterThanOrEqual(32, $secret->encodedLength());
        self::assertSame('[REDACTED]', (string) $secret);
    }

    public function testCodeGenerationMatchesRfc6238Sha1Vector(): void
    {
        $parameters = new TotpParameters(TotpHashAlgorithm::sha1(), 8, 30, 0, 0);
        $code = (new NativeTotpCodeGenerator())->generate(
            new TotpSecret(self::RFC_SECRET),
            $parameters,
            new DateTimeImmutable('@59')
        );

        self::assertSame('94287082', $code->expose(static fn (string $value): string => $value));
    }

    public function testVerifierAcceptsExactRfc6238Code(): void
    {
        $parameters = new TotpParameters(TotpHashAlgorithm::sha1(), 8, 30, 0, 0);
        $result = (new NativeTotpVerifier())->verify(
            new TotpSecret(self::RFC_SECRET),
            new TotpCode('94287082'),
            $parameters,
            new DateTimeImmutable('@59')
        );

        self::assertTrue($result->isVerified());
        self::assertSame(1, $result->matchedCounter());
    }

    public function testVerifierHonorsPastAndFutureWindows(): void
    {
        $secret = new TotpSecret(self::RFC_SECRET);
        $parameters = new TotpParameters(TotpHashAlgorithm::sha1(), 8, 30, 1, 1);
        $generator = new NativeTotpCodeGenerator();
        $verifier = new NativeTotpVerifier($generator);
        $previousCode = $generator->generateForCounter($secret, $parameters, 1);

        $result = $verifier->verify($secret, $previousCode, $parameters, new DateTimeImmutable('@89'));

        self::assertTrue($result->isVerified());
        self::assertSame(1, $result->matchedCounter());
    }

    public function testVerifierRejectsWrongCodeAndDigitMismatch(): void
    {
        $secret = new TotpSecret(self::RFC_SECRET);
        $parameters = new TotpParameters(TotpHashAlgorithm::sha1(), 8, 30, 0, 0);
        $verifier = new NativeTotpVerifier();

        self::assertFalse(
            $verifier->verify($secret, new TotpCode('00000000'), $parameters, new DateTimeImmutable('@59'))->isVerified()
        );
        self::assertFalse(
            $verifier->verify($secret, new TotpCode('123456'), $parameters, new DateTimeImmutable('@59'))->isVerified()
        );
    }
}
