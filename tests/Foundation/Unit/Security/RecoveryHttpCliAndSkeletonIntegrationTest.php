<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Cli\Command\Security\RecoveryChallengeInspectCommand;
use Sif\Foundation\Cli\Command\Security\RecoveryChallengeRevokeCommand;
use Sif\Foundation\Cli\Value\CliCommandName;
use Sif\Foundation\Cli\Value\CliInvocation;
use Sif\Foundation\Security\Http\Recovery\RecoveryConfirmationPayload;
use Sif\Foundation\Security\Http\Recovery\RecoveryRequestPayload;
use Sif\Foundation\Security\Recovery\InMemoryRecoveryChallengeStore;
use Sif\Foundation\Security\Recovery\RecoveryChallenge;
use Sif\Foundation\Security\Recovery\RecoveryChallengeId;
use Sif\Foundation\Security\Recovery\RecoveryChallengePurpose;
use Sif\Foundation\Security\Recovery\RecoveryChallengeRecord;
use Sif\Foundation\Security\Recovery\RecoverySubjectKey;
use Sif\Foundation\Security\Recovery\RecoveryToken;
use Sif\Foundation\Security\Recovery\RecoveryTokenDigest;

final class RecoveryHttpCliAndSkeletonIntegrationTest extends TestCase
{
    public function testRequestAndConfirmationPayloadsAreParsedWithoutDebugExposure(): void
    {
        $request = RecoveryRequestPayload::fromJson('{"identity":"alice@example.test"}');
        self::assertSame('alice@example.test', $request->lookupKey()->value());

        $token = str_repeat('A', 43);
        $confirmation = RecoveryConfirmationPayload::fromJson('{"challenge_id":"challenge-1","token":"'.$token.'"}');
        self::assertSame('challenge-1', $confirmation->challengeId()->value());
        self::assertSame('[REDACTED]', (string) $confirmation->token());
    }

    public function testCliInspectReturnsSanitizedSnapshot(): void
    {
        $store = $this->storeWithChallenge();
        $command = new RecoveryChallengeInspectCommand($store);
        $result = $command->execute(new CliInvocation(new CliCommandName('security:recovery:inspect'), ['challenge-1']));
        self::assertTrue($result->exitCode()->successful());
        $json = json_encode($result->data(), JSON_THROW_ON_ERROR);
        self::assertStringNotContainsString(str_repeat('A', 43), $json);
        self::assertStringContainsString('subject_fingerprint', $json);
    }

    public function testCliRevokeTransitionsPendingChallenge(): void
    {
        $store = $this->storeWithChallenge();
        $command = new RecoveryChallengeRevokeCommand($store);
        $result = $command->execute(new CliInvocation(new CliCommandName('security:recovery:revoke'), ['challenge-1']));
        self::assertTrue($result->exitCode()->successful());
        self::assertSame('revoked', $store->find(new RecoveryChallengeId('challenge-1'))?->state()->value);
    }

    public function testCommandMetadataIsExplicitlyOptIn(): void
    {
        $store = new InMemoryRecoveryChallengeStore();
        self::assertSame('security:recovery:inspect', (new RecoveryChallengeInspectCommand($store))->metadata()->name()->value());
        self::assertSame('security:recovery:revoke', (new RecoveryChallengeRevokeCommand($store))->metadata()->name()->value());
    }

    private function storeWithChallenge(): InMemoryRecoveryChallengeStore
    {
        $store = new InMemoryRecoveryChallengeStore();
        $token = new RecoveryToken(str_repeat('A', 43));
        $store->issue(new RecoveryChallengeRecord(
            new RecoveryChallenge(
                new RecoveryChallengeId('challenge-1'),
                RecoveryChallengePurpose::PasswordReset,
                new RecoverySubjectKey('identity-1'),
                new DateTimeImmutable('2026-08-06T12:00:00+00:00'),
                new DateTimeImmutable('2026-08-06T13:00:00+00:00')
            ),
            RecoveryTokenDigest::fromToken($token)
        ));
        return $store;
    }
}
