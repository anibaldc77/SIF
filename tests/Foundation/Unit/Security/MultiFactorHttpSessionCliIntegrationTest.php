<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Cli\Command\Security\MultiFactorChallengeInspectCommand;
use Sif\Foundation\Cli\Command\Security\MultiFactorChallengeRevokeCommand;
use Sif\Foundation\Cli\Value\CliCommandName;
use Sif\Foundation\Cli\Value\CliInvocation;
use Sif\Foundation\Security\Authentication\AuthenticationEvidence;
use Sif\Foundation\Security\Authentication\AuthenticationLevel;
use Sif\Foundation\Security\Authentication\AuthenticationMethod;
use Sif\Foundation\Security\Context\SecurityContext;
use Sif\Foundation\Security\Http\MultiFactor\MultiFactorSessionElevationService;
use Sif\Foundation\Security\Http\MultiFactor\RecoveryCodeChallengeResponsePayload;
use Sif\Foundation\Security\Http\MultiFactor\TotpChallengeResponsePayload;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;
use Sif\Foundation\Security\Identity\Identity;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\Identity\PrincipalAttributeCollection;
use Sif\Foundation\Security\MultiFactor\InMemoryMultiFactorChallengeStore;
use Sif\Foundation\Security\MultiFactor\MultiFactorChallengePurpose;
use Sif\Foundation\Security\MultiFactor\RecoveryCode\InMemoryRecoveryCodeStore;
use Sif\Foundation\Security\MultiFactor\RecoveryCode\RecoveryCode;
use Sif\Foundation\Security\MultiFactor\RecoveryCode\RecoveryCodeEnrollmentService;
use Sif\Foundation\Security\MultiFactor\RecoveryCode\RecoveryCodeGenerator;
use Sif\Foundation\Security\MultiFactor\RecoveryCode\RecoveryCodeMultiFactorService;
use Sif\Foundation\Security\MultiFactor\Totp\InMemoryTotpFactorStore;
use Sif\Foundation\Security\MultiFactor\Totp\NativeTotpCodeGenerator;
use Sif\Foundation\Security\MultiFactor\Totp\NativeTotpVerifier;
use Sif\Foundation\Security\MultiFactor\Totp\TotpFactorVerifier;
use Sif\Foundation\Security\MultiFactor\Totp\TotpMultiFactorService;
use Sif\Foundation\Security\Session\SessionAuthenticationManager;
use Sif\Foundation\Session\SessionId;
use Sif\Foundation\Session\SessionState;

final class MultiFactorHttpSessionCliIntegrationTest extends TestCase
{
    public function testSensitivePayloadsAreRedactedAndNotSerializable(): void
    {
        $totp = TotpChallengeResponsePayload::fromJson(
            '{"challenge_id":"challenge-1","code":"123456"}'
        );
        self::assertSame('[REDACTED]', $totp->__debugInfo()['code']);

        $recovery = RecoveryCodeChallengeResponsePayload::fromJson(
            '{"challenge_id":"challenge-2","code":"AAAAA-BBBBB-CCCCC-DDDDD"}'
        );
        self::assertSame('[REDACTED]', $recovery->__debugInfo()['code']);

        $this->expectException(\LogicException::class);
        serialize($recovery);
    }

    public function testRecoveryCodeSatisfactionElevatesSessionPrincipal(): void
    {
        $now = new DateTimeImmutable('2026-08-06T22:00:00+00:00');
        $principal = $this->principal($now);
        $context = new SecurityContext($principal);
        $session = new SessionState(
            new SessionId(str_repeat('a', 32)),
            [],
            $now,
            $now
        );

        $challengeStore = new InMemoryMultiFactorChallengeStore();
        $codeStore = new InMemoryRecoveryCodeStore();
        $batch = (new RecoveryCodeEnrollmentService(
            new RecoveryCodeGenerator(1),
            $codeStore
        ))->replaceForIdentity($principal->identity()->id(), $now);

        $recoveryService = new RecoveryCodeMultiFactorService(
            $challengeStore,
            $codeStore
        );
        $challenge = $recoveryService->issue(
            $principal,
            MultiFactorChallengePurpose::StepUp,
            new AuthenticationLevel(70),
            $now
        );
        self::assertNotNull($challenge);

        $totpService = new TotpMultiFactorService(
            $challengeStore,
            new InMemoryTotpFactorStore(),
            new TotpFactorVerifier(
                new NativeTotpVerifier(new NativeTotpCodeGenerator()),
                new InMemoryTotpFactorStore()
            )
        );

        $elevation = new MultiFactorSessionElevationService(
            $totpService,
            $recoveryService,
            new SessionAuthenticationManager(),
            $context
        );

        $code = $batch->expose(
            static fn (array $codes): RecoveryCode => $codes[0]
        );
        $payload = $code->expose(
            static fn (string $value): RecoveryCodeChallengeResponsePayload =>
                RecoveryCodeChallengeResponsePayload::fromJson(
                    json_encode([
                        'challenge_id' => $challenge->id()->value(),
                        'code' => $value,
                    ], JSON_THROW_ON_ERROR)
                )
        );

        $result = $elevation->satisfyRecoveryCode($payload, $session, $now);

        self::assertTrue($result->isSatisfied());
        self::assertTrue($session->has(SessionAuthenticationManager::SESSION_KEY));
        self::assertTrue($session->regenerationRequested());
        $elevatedPrincipal = $context->principal();
        self::assertInstanceOf(AuthenticatedPrincipal::class, $elevatedPrincipal);

        self::assertSame(
            70,
            $elevatedPrincipal->evidence()->level()->value()
        );
    }

    public function testCliInspectReturnsSanitizedChallengeSnapshot(): void
    {
        [$store, $challengeId] = $this->challengeStore();

        $result = (new MultiFactorChallengeInspectCommand($store))->execute(
            new CliInvocation(
                new CliCommandName('security:mfa:challenge:inspect'),
                [$challengeId]
            )
        );

        self::assertTrue($result->exitCode()->successful());
        $json = json_encode($result->data(), JSON_THROW_ON_ERROR);
        self::assertStringContainsString('identity_fingerprint', $json);
        self::assertStringNotContainsString('user-42', $json);
    }

    public function testCliRevokeTransitionsPendingChallenge(): void
    {
        [$store, $challengeId] = $this->challengeStore();

        $result = (new MultiFactorChallengeRevokeCommand($store))->execute(
            new CliInvocation(
                new CliCommandName('security:mfa:challenge:revoke'),
                [$challengeId]
            )
        );

        self::assertTrue($result->exitCode()->successful());
        self::assertSame(
            'revoked',
            $store->find(
                new \Sif\Foundation\Security\MultiFactor\MultiFactorChallengeId(
                    $challengeId
                )
            )?->status()->value
        );
    }

    /** @return array{InMemoryMultiFactorChallengeStore,string} */
    private function challengeStore(): array
    {
        $now = new DateTimeImmutable('2026-08-06T22:00:00+00:00');
        $principal = $this->principal($now);
        $store = new InMemoryMultiFactorChallengeStore();
        $codeStore = new InMemoryRecoveryCodeStore();
        (new RecoveryCodeEnrollmentService(
            new RecoveryCodeGenerator(1),
            $codeStore
        ))->replaceForIdentity($principal->identity()->id(), $now);

        $challenge = (new RecoveryCodeMultiFactorService(
            $store,
            $codeStore
        ))->issue(
            $principal,
            MultiFactorChallengePurpose::StepUp,
            new AuthenticationLevel(70),
            $now
        );
        self::assertNotNull($challenge);

        return [$store, $challenge->id()->value()];
    }

    private function principal(DateTimeImmutable $now): AuthenticatedPrincipal
    {
        return new AuthenticatedPrincipal(
            new Identity(new IdentityId('user-42')),
            new PrincipalAttributeCollection(),
            new AuthenticationEvidence(
                new AuthenticationMethod('password'),
                new AuthenticationLevel(50),
                $now->modify('-5 minutes')
            )
        );
    }
}
