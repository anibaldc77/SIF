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
use Sif\Foundation\Security\MultiFactor\Totp\Base32Codec;
use Sif\Foundation\Security\MultiFactor\Totp\InMemoryTotpFactorStore;
use Sif\Foundation\Security\MultiFactor\Totp\NativeTotpCodeGenerator;
use Sif\Foundation\Security\MultiFactor\Totp\NativeTotpVerifier;
use Sif\Foundation\Security\MultiFactor\Totp\TotpCode;
use Sif\Foundation\Security\MultiFactor\Totp\TotpFactorId;
use Sif\Foundation\Security\MultiFactor\Totp\TotpFactorRecord;
use Sif\Foundation\Security\MultiFactor\Totp\TotpFactorVerifier;
use Sif\Foundation\Security\MultiFactor\Totp\TotpMultiFactorService;
use Sif\Foundation\Security\MultiFactor\Totp\TotpParameters;
use Sif\Foundation\Security\MultiFactor\Totp\TotpSecret;

final class TotpMultiFactorChallengeAndStepUpAuthenticationTest extends TestCase
{
    private const SECRET = 'JBSWY3DPEHPK3PXP';

    public function testChallengeIsIssuedForActiveTotpFactor(): void
    {
        [$service] = $this->service();
        $challenge = $service->issue($this->principal(), MultiFactorChallengePurpose::StepUp,
            new AuthenticationLevel(70), $this->now());
        self::assertNotNull($challenge);
        self::assertSame('totp', $challenge->factorType()->value());
        self::assertSame(70, $challenge->requiredLevel()->value());
    }

    public function testNoChallengeIsIssuedWhenCurrentLevelAlreadySatisfiesRequirement(): void
    {
        [$service] = $this->service();
        self::assertNull($service->issue($this->principal(80), MultiFactorChallengePurpose::StepUp,
            new AuthenticationLevel(70), $this->now()));
    }

    public function testValidTotpSatisfiesChallengeAndElevatesPrincipal(): void
    {
        [$service, $challenges] = $this->service();
        $challenge = $service->issue($this->principal(), MultiFactorChallengePurpose::StepUp,
            new AuthenticationLevel(70), $this->now());
        self::assertNotNull($challenge);
        $code = (new NativeTotpCodeGenerator())->generate(new TotpSecret(self::SECRET), TotpParameters::rfc6238(), $this->now());
        $result = $service->satisfy($this->principal(), $challenge->id(), $code, $this->now());
        self::assertTrue($result->isSatisfied());
        self::assertSame(70, $result->principal()?->evidence()->level()->value());
        self::assertSame(MultiFactorChallengeStatus::Satisfied, $challenges->find($challenge->id())?->status());
    }

    public function testWrongCodeDoesNotSatisfyChallenge(): void
    {
        [$service, $challenges] = $this->service();
        $challenge = $service->issue($this->principal(), MultiFactorChallengePurpose::StepUp,
            new AuthenticationLevel(70), $this->now());
        self::assertNotNull($challenge);
        $result = $service->satisfy($this->principal(), $challenge->id(), new TotpCode('000000'), $this->now());
        self::assertFalse($result->isSatisfied());
        self::assertSame(MultiFactorChallengeStatus::Pending, $challenges->find($challenge->id())?->status());
    }

    public function testSatisfiedChallengeCannotBeReplayed(): void
    {
        [$service] = $this->service();
        $challenge = $service->issue($this->principal(), MultiFactorChallengePurpose::StepUp,
            new AuthenticationLevel(70), $this->now());
        self::assertNotNull($challenge);
        $code = (new NativeTotpCodeGenerator())->generate(new TotpSecret(self::SECRET), TotpParameters::rfc6238(), $this->now());
        self::assertTrue($service->satisfy($this->principal(), $challenge->id(), $code, $this->now())->isSatisfied());
        self::assertFalse($service->satisfy($this->principal(), $challenge->id(), $code, $this->now())->isSatisfied());
    }

    /** @return array{TotpMultiFactorService, InMemoryMultiFactorChallengeStore} */
    private function service(): array
    {
        $factorStore = new InMemoryTotpFactorStore($this->now());
        $factor = TotpFactorRecord::pending(new TotpFactorId('factor-1'), new IdentityId('user-42'),
            new TotpSecret(self::SECRET), TotpParameters::rfc6238(), $this->now()->modify('-1 minute'));
        $factorStore->save($factor);
        $counter = intdiv($this->now()->getTimestamp(), 30) - 1;
        self::assertTrue($factorStore->activate($factor->id(), $counter));
        $challenges = new InMemoryMultiFactorChallengeStore();
        return [new TotpMultiFactorService($challenges, $factorStore,
            new TotpFactorVerifier(new NativeTotpVerifier(new NativeTotpCodeGenerator(new Base32Codec())), $factorStore)), $challenges];
    }

    private function principal(int $level = 50): AuthenticatedPrincipal
    {
        return new AuthenticatedPrincipal(new Identity(new IdentityId('user-42')), new PrincipalAttributeCollection(),
            new AuthenticationEvidence(new AuthenticationMethod('password'), new AuthenticationLevel($level), $this->now()->modify('-5 minutes')));
    }

    private function now(): DateTimeImmutable { return new DateTimeImmutable('2026-08-06T20:00:00+00:00'); }
}
