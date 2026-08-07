<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\PersistentAuthentication\InMemoryPersistentAuthenticationCredentialStore;
use Sif\Foundation\Security\PersistentAuthentication\PersistentAuthenticationService;
use Sif\Foundation\Security\PersistentAuthentication\PersistentAuthenticationToken;
use Sif\Foundation\Security\PersistentAuthentication\PersistentAuthenticationValidationStatus;
use Sif\Foundation\Security\PersistentAuthentication\SecurePersistentAuthenticationTokenGenerator;

final class PersistentCredentialLifecycleRotationAndReplayDetectionTest extends TestCase
{
    public function testIssuedCredentialStoresDigestAndPreservesAbsoluteExpiration(): void
    {
        [$service, $store] = $this->service();
        $issuedAt = $this->now();
        $expiresAt = $issuedAt->modify('+30 days');

        $token = $service->issue(
            new IdentityId('user-rotation-1'),
            $issuedAt,
            $expiresAt
        );

        $credential = $store->findBySelector($token->selector());

        self::assertNotNull($credential);
        self::assertTrue(
            $credential->validatorDigest()->equals(
                $token->validatorDigest()
            )
        );
        self::assertSame(
            $expiresAt->format(DATE_ATOM),
            $credential->absoluteExpiresAt()->format(DATE_ATOM)
        );
    }

    public function testSuccessfulValidationRotatesValidatorButKeepsSelectorAndAbsoluteExpiration(): void
    {
        [$service, $store] = $this->service();
        $issuedAt = $this->now();
        $expiresAt = $issuedAt->modify('+30 days');

        $token = $service->issue(
            new IdentityId('user-rotation-2'),
            $issuedAt,
            $expiresAt
        );
        $oldDigest = $token->validatorDigest();

        $result = $service->validateAndRotate(
            $token,
            $issuedAt->modify('+1 hour')
        );

        self::assertTrue($result->isAccepted());
        $replacement = $result->replacementToken();
        self::assertNotNull($replacement);
        self::assertTrue(
            $replacement->selector()->equals($token->selector())
        );
        self::assertFalse(
            $replacement->validatorDigest()->equals($oldDigest)
        );

        $credential = $store->findBySelector($token->selector());
        self::assertNotNull($credential);
        self::assertTrue(
            $credential->validatorDigest()->equals(
                $replacement->validatorDigest()
            )
        );
        self::assertSame(
            $expiresAt->format(DATE_ATOM),
            $credential->absoluteExpiresAt()->format(DATE_ATOM)
        );
    }

    public function testReusingPreviousValidatorIsDetectedAsReplayAndRevokesCredential(): void
    {
        [$service, $store] = $this->service();
        $issuedAt = $this->now();

        $token = $service->issue(
            new IdentityId('user-replay-1'),
            $issuedAt,
            $issuedAt->modify('+30 days')
        );

        self::assertTrue(
            $service->validateAndRotate(
                $token,
                $issuedAt->modify('+1 hour')
            )->isAccepted()
        );

        $replay = $service->validateAndRotate(
            $token,
            $issuedAt->modify('+2 hours')
        );

        self::assertSame(
            PersistentAuthenticationValidationStatus::ReplaySuspected,
            $replay->status()
        );

        $credential = $store->findBySelector($token->selector());
        self::assertNotNull($credential);
        self::assertSame('revoked', $credential->status()->value);
    }

    public function testExpiredCredentialIsRevokedAndCannotRotate(): void
    {
        [$service, $store] = $this->service();
        $issuedAt = $this->now();

        $token = $service->issue(
            new IdentityId('user-expired-1'),
            $issuedAt,
            $issuedAt->modify('+1 day')
        );

        $result = $service->validateAndRotate(
            $token,
            $issuedAt->modify('+2 days')
        );

        self::assertSame(
            PersistentAuthenticationValidationStatus::Expired,
            $result->status()
        );

        $credential = $store->findBySelector($token->selector());
        self::assertNotNull($credential);
        self::assertSame('revoked', $credential->status()->value);
    }

    public function testExplicitRevocationPreventsFutureValidation(): void
    {
        [$service] = $this->service();
        $issuedAt = $this->now();

        $token = $service->issue(
            new IdentityId('user-revoked-1'),
            $issuedAt,
            $issuedAt->modify('+30 days')
        );

        self::assertTrue(
            $service->revoke(
                $token->selector(),
                $issuedAt->modify('+1 minute')
            )
        );

        $result = $service->validateAndRotate(
            $token,
            $issuedAt->modify('+2 minutes')
        );

        self::assertSame(
            PersistentAuthenticationValidationStatus::Revoked,
            $result->status()
        );
    }

    public function testUnknownSelectorDoesNotRevealAnyCredentialState(): void
    {
        [$service] = $this->service();

        $token = (new SecurePersistentAuthenticationTokenGenerator())->generate();

        $result = $service->validateAndRotate($token, $this->now());

        self::assertSame(
            PersistentAuthenticationValidationStatus::Missing,
            $result->status()
        );
        self::assertNull($result->replacementToken());
    }

    /** @return array{PersistentAuthenticationService,InMemoryPersistentAuthenticationCredentialStore} */
    private function service(): array
    {
        $store = new InMemoryPersistentAuthenticationCredentialStore();

        return [
            new PersistentAuthenticationService(
                $store,
                new SecurePersistentAuthenticationTokenGenerator()
            ),
            $store,
        ];
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-08-07T12:00:00+00:00');
    }
}
