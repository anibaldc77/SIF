<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\RecoverySecurityEventHandlerInterface;
use Sif\Foundation\Security\IdentityProvider\IdentityLookupKey;
use Sif\Foundation\Security\IdentityProvider\IdentityProviderId;
use Sif\Foundation\Security\Recovery\Events\RecoverySecurityEvent;
use Sif\Foundation\Security\Recovery\Events\RecoverySecurityEventType;
use Sif\Foundation\Security\Recovery\Protection\InMemoryRecoveryRequestProtector;
use Sif\Foundation\Security\Recovery\Protection\RecoveryRequestKey;
use Sif\Foundation\Security\Recovery\Protection\RecoveryRequestProtectionPolicy;
use Sif\Foundation\Security\Recovery\RecoveryChallengePurpose;

final class RecoveryAbuseProtectionAndSecurityEventsTest extends TestCase
{
    public function testProtectionBlocksAfterConfiguredRequestLimit(): void
    {
        $protector = new InMemoryRecoveryRequestProtector(new RecoveryRequestProtectionPolicy(maximumRequests: 2));
        $key = new RecoveryRequestKey(
            new IdentityProviderId('provider'),
            new IdentityLookupKey('user@example.test'),
            RecoveryChallengePurpose::PasswordReset
        );
        $instant = new DateTimeImmutable('2026-08-06T12:00:00Z');

        self::assertTrue($protector->assess($key, $instant)->isAllowed());
        $protector->record($key, $instant);
        self::assertTrue($protector->assess($key, $instant->modify('+1 minute'))->isAllowed());
        $protector->record($key, $instant->modify('+1 minute'));
        self::assertFalse($protector->assess($key, $instant->modify('+2 minutes'))->isAllowed());
    }

    public function testFingerprintsAreStableAndDoNotExposeLookup(): void
    {
        $key = new RecoveryRequestKey(
            new IdentityProviderId('provider'),
            new IdentityLookupKey('secret@example.test'),
            RecoveryChallengePurpose::IdentityVerification
        );

        self::assertSame(64, strlen($key->fingerprint()));
        self::assertStringNotContainsString('secret@example.test', $key->fingerprint());
    }

    public function testSecurityEventSnapshotContainsNoTokenOrLookup(): void
    {
        $event = new RecoverySecurityEvent(
            RecoverySecurityEventType::RequestBlocked,
            RecoveryChallengePurpose::PasswordReset,
            str_repeat('a', 64),
            new DateTimeImmutable('2026-08-06T12:00:00Z')
        );
        $snapshot = $event->snapshot();
        $encoded = json_encode($snapshot, JSON_THROW_ON_ERROR);

        self::assertSame('request_blocked', $snapshot['type']);
        self::assertArrayNotHasKey('token', $snapshot);
        self::assertArrayNotHasKey('digest', $snapshot);
        self::assertArrayNotHasKey('lookup', $snapshot);
        self::assertArrayNotHasKey('credential', $snapshot);
        self::assertArrayNotHasKey('secret', $snapshot);
        self::assertStringNotContainsString('user@example.test', $encoded);
        self::assertStringNotContainsString('opaque-recovery-token', $encoded);
    }

    public function testEventHandlerContractCanCaptureEvents(): void
    {
        $handler = new RecoveryCapturingEventHandler();
        $event = new RecoverySecurityEvent(
            RecoverySecurityEventType::ChallengeConsumed,
            RecoveryChallengePurpose::IdentityVerification,
            str_repeat('b', 64),
            new DateTimeImmutable('2026-08-06T12:00:00Z')
        );

        $handler->handle($event);

        self::assertSame([$event], $handler->events);
    }
}

final class RecoveryCapturingEventHandler implements RecoverySecurityEventHandlerInterface
{
    /** @var list<RecoverySecurityEvent> */
    public array $events = [];

    public function handle(RecoverySecurityEvent $event): void
    {
        $this->events[] = $event;
    }
}
