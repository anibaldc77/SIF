<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Authentication\AuthenticationEvidence;
use Sif\Foundation\Security\Authentication\AuthenticationLevel;
use Sif\Foundation\Security\Authentication\AuthenticationMethod;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;
use Sif\Foundation\Security\Identity\Identity;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\Identity\PrincipalAttributeCollection;
use Sif\Foundation\Security\MultiFactor\InMemoryMultiFactorChallengeStore;
use Sif\Foundation\Security\MultiFactor\MultiFactorChallengePurpose;
use Sif\Foundation\Security\MultiFactor\MultiFactorChallengeStatus;
use Sif\Foundation\Security\MultiFactor\RecoveryCode\InMemoryRecoveryCodeStore;
use Sif\Foundation\Security\MultiFactor\RecoveryCode\RecoveryCode;
use Sif\Foundation\Security\MultiFactor\RecoveryCode\RecoveryCodeEnrollmentService;
use Sif\Foundation\Security\MultiFactor\RecoveryCode\RecoveryCodeGenerator;
use Sif\Foundation\Security\MultiFactor\RecoveryCode\RecoveryCodeMultiFactorService;

final class RecoveryCodeAlternativeMultiFactorTest extends TestCase
{
    public function testEnrollmentStoresOnlyDigestsAndReturnsRedactedBatch(): void
    {
        [$service, $store, $batch] = $this->service();

        self::assertSame(2, $batch->count());
        self::assertStringNotContainsString('RecoveryCode', (string) json_encode($store->recordsForIdentity(new IdentityId('user-42'))));
        self::assertSame('[REDACTED]', $batch->__debugInfo()['codes']);
        self::assertNotNull($service);
    }

    public function testChallengeIsIssuedWhenRecoveryCodeIsAvailable(): void
    {
        [$service] = $this->service();
        $challenge = $service->issue(
            $this->principal(),
            MultiFactorChallengePurpose::StepUp,
            new AuthenticationLevel(70),
            $this->now()
        );

        self::assertNotNull($challenge);
        self::assertSame('recovery_code', $challenge->factorType()->value());
    }

    public function testValidRecoveryCodeElevatesPrincipalAndConsumesCode(): void
    {
        [$service, $store, $batch, $challenges] = $this->service();
        $challenge = $service->issue(
            $this->principal(),
            MultiFactorChallengePurpose::StepUp,
            new AuthenticationLevel(70),
            $this->now()
        );
        self::assertNotNull($challenge);

        $code = $batch->expose(static fn (array $codes): RecoveryCode => $codes[0]);
        $result = $service->satisfy($this->principal(), $challenge->id(), $code, $this->now());

        self::assertTrue($result->isSatisfied());

        $elevatedPrincipal = $result->principal();
        self::assertNotNull($elevatedPrincipal);

        self::assertSame(70, $elevatedPrincipal->evidence()->level()->value());
        self::assertSame('mfa.recovery_code', $elevatedPrincipal->evidence()->method()->value());
        self::assertSame(MultiFactorChallengeStatus::Satisfied, $challenges->find($challenge->id())?->status());
        self::assertTrue($store->recordsForIdentity(new IdentityId('user-42'))[0]->isConsumed());
    }

    public function testConsumedRecoveryCodeCannotBeReused(): void
    {
        [$service, , $batch] = $this->service();
        $code = $batch->expose(static fn (array $codes): RecoveryCode => $codes[0]);

        $first = $service->issue(
            $this->principal(),
            MultiFactorChallengePurpose::StepUp,
            new AuthenticationLevel(70),
            $this->now()
        );
        self::assertNotNull($first);
        self::assertTrue($service->satisfy($this->principal(), $first->id(), $code, $this->now())->isSatisfied());

        $second = $service->issue(
            $this->principal(),
            MultiFactorChallengePurpose::StepUp,
            new AuthenticationLevel(70),
            $this->now()->modify('+1 minute')
        );
        self::assertNotNull($second);
        self::assertFalse(
            $service->satisfy($this->principal(), $second->id(), $code, $this->now()->modify('+1 minute'))->isSatisfied()
        );
    }

    public function testWrongCodeDoesNotConsumeAvailableCodes(): void
    {
        [$service, $store] = $this->service();
        $challenge = $service->issue(
            $this->principal(),
            MultiFactorChallengePurpose::StepUp,
            new AuthenticationLevel(70),
            $this->now()
        );
        self::assertNotNull($challenge);

        self::assertFalse(
            $service->satisfy(
                $this->principal(),
                $challenge->id(),
                new RecoveryCode('AAAAA-BBBBB-CCCCC-DDDDD'),
                $this->now()
            )->isSatisfied()
        );
        self::assertTrue($store->hasAvailableForIdentity(new IdentityId('user-42')));
    }

    /** @return array{RecoveryCodeMultiFactorService, InMemoryRecoveryCodeStore, \Sif\Foundation\Security\MultiFactor\RecoveryCode\RecoveryCodeBatch, InMemoryMultiFactorChallengeStore} */
    private function service(): array
    {
        $store = new InMemoryRecoveryCodeStore();
        $batch = (new RecoveryCodeEnrollmentService(new RecoveryCodeGenerator(2), $store))
            ->replaceForIdentity(new IdentityId('user-42'), $this->now());
        $challenges = new InMemoryMultiFactorChallengeStore();

        return [new RecoveryCodeMultiFactorService($challenges, $store), $store, $batch, $challenges];
    }

    private function principal(): AuthenticatedPrincipal
    {
        return new AuthenticatedPrincipal(
            new Identity(new IdentityId('user-42')),
            new PrincipalAttributeCollection(),
            new AuthenticationEvidence(
                new AuthenticationMethod('password'),
                new AuthenticationLevel(50),
                $this->now()->modify('-5 minutes')
            )
        );
    }

    private function now(): DateTimeImmutable
    {
        return new DateTimeImmutable('2026-08-06T21:30:00+00:00');
    }
}
