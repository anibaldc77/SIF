<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\MultiFactor\Totp\InMemoryTotpFactorStore;
use Sif\Foundation\Security\MultiFactor\Totp\NativeTotpCodeGenerator;
use Sif\Foundation\Security\MultiFactor\Totp\NativeTotpVerifier;
use Sif\Foundation\Security\MultiFactor\Totp\TotpCode;
use Sif\Foundation\Security\MultiFactor\Totp\TotpEnrollmentService;
use Sif\Foundation\Security\MultiFactor\Totp\TotpFactorId;
use Sif\Foundation\Security\MultiFactor\Totp\TotpFactorStatus;
use Sif\Foundation\Security\MultiFactor\Totp\TotpFactorVerifier;
use Sif\Foundation\Security\MultiFactor\Totp\TotpHashAlgorithm;
use Sif\Foundation\Security\MultiFactor\Totp\TotpParameters;
use Sif\Foundation\Security\MultiFactor\Totp\TotpSecret;
use Sif\Foundation\Security\Contracts\TotpSecretGeneratorInterface;

final class TotpFactorEnrollmentActivationAndReplayProtectionTest extends TestCase
{
    private const SECRET = 'GEZDGNBVGY3TQOJQGEZDGNBVGY3TQOJQ';

    public function testEnrollmentStoresPendingFactorWithoutExposingSecretInSnapshot(): void
    {
        $store = new InMemoryTotpFactorStore(new DateTimeImmutable('@59'));
        $service = $this->enrollmentService($store);
        $factorId = new TotpFactorId('totp:user-1001');

        $result = $service->begin(
            $factorId,
            new IdentityId('user-1001'),
            new DateTimeImmutable('@30')
        );
        $factor = $store->find($factorId);

        self::assertNotNull($factor);
        self::assertSame(TotpFactorStatus::Pending, $factor->status());
        self::assertSame('[REDACTED]', (string) $result->secret());
        self::assertArrayNotHasKey('secret', $factor->snapshot());
        self::assertSame(64, strlen($factor->snapshot()['identity_fingerprint']));
    }

    public function testValidCodeActivatesPendingFactorAndStoresCounter(): void
    {
        $store = new InMemoryTotpFactorStore(new DateTimeImmutable('@59'));
        $service = $this->enrollmentService($store);
        $factorId = new TotpFactorId('totp:user-1002');
        $service->begin($factorId, new IdentityId('user-1002'), new DateTimeImmutable('@30'));

        $result = $service->activate($factorId, new TotpCode('94287082'), new DateTimeImmutable('@59'));
        $factor = $store->find($factorId);

        self::assertTrue($result->isVerified());
        self::assertNotNull($factor);
        self::assertSame(TotpFactorStatus::Active, $factor->status());
        self::assertSame(1, $factor->lastAcceptedCounter());
    }

    public function testWrongCodeDoesNotActivateFactor(): void
    {
        $store = new InMemoryTotpFactorStore(new DateTimeImmutable('@59'));
        $service = $this->enrollmentService($store);
        $factorId = new TotpFactorId('totp:user-1003');
        $service->begin($factorId, new IdentityId('user-1003'), new DateTimeImmutable('@30'));

        self::assertFalse(
            $service->activate($factorId, new TotpCode('00000000'), new DateTimeImmutable('@59'))->isVerified()
        );
        self::assertSame(TotpFactorStatus::Pending, $store->find($factorId)?->status());
    }

    public function testActiveFactorAcceptsOnlyStrictlyNewerCounters(): void
    {
        $store = new InMemoryTotpFactorStore(new DateTimeImmutable('@59'));
        $service = $this->enrollmentService($store);
        $identityId = new IdentityId('user-1004');
        $factorId = new TotpFactorId('totp:user-1004');
        $service->begin($factorId, $identityId, new DateTimeImmutable('@30'));
        self::assertTrue($service->activate($factorId, new TotpCode('94287082'), new DateTimeImmutable('@59'))->isVerified());

        $generator = new NativeTotpCodeGenerator();
        $parameters = new TotpParameters(TotpHashAlgorithm::sha1(), 8, 30, 0, 0);
        $nextCode = $generator->generateForCounter(new TotpSecret(self::SECRET), $parameters, 2);
        $verifier = new TotpFactorVerifier(new NativeTotpVerifier($generator), $store);

        self::assertFalse($verifier->verify($identityId, new TotpCode('94287082'), new DateTimeImmutable('@59'))->isVerified());
        self::assertTrue($verifier->verify($identityId, $nextCode, new DateTimeImmutable('@60'))->isVerified());
        self::assertSame(2, $store->find($factorId)?->lastAcceptedCounter());
    }

    public function testRevokedFactorCannotBeUsed(): void
    {
        $store = new InMemoryTotpFactorStore(new DateTimeImmutable('@59'));
        $service = $this->enrollmentService($store);
        $identityId = new IdentityId('user-1005');
        $factorId = new TotpFactorId('totp:user-1005');
        $service->begin($factorId, $identityId, new DateTimeImmutable('@30'));
        self::assertTrue($service->activate($factorId, new TotpCode('94287082'), new DateTimeImmutable('@59'))->isVerified());
        self::assertTrue($store->revoke($factorId));

        self::assertFalse(
            (new TotpFactorVerifier(new NativeTotpVerifier(), $store))
                ->verify($identityId, new TotpCode('94287082'), new DateTimeImmutable('@59'))
                ->isVerified()
        );
    }

    private function enrollmentService(InMemoryTotpFactorStore $store): TotpEnrollmentService
    {
        $parameters = new TotpParameters(TotpHashAlgorithm::sha1(), 8, 30, 0, 0);
        $generator = new class(self::SECRET) implements TotpSecretGeneratorInterface {
            public function __construct(private readonly string $secret)
            {
            }

            public function generate(): TotpSecret
            {
                return new TotpSecret($this->secret);
            }
        };

        return new TotpEnrollmentService(
            $generator,
            new NativeTotpVerifier(),
            $store,
            $parameters
        );
    }
}
