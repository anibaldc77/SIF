<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Integration\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Authentication\AuthenticationEvidence;
use Sif\Foundation\Security\Authentication\AuthenticationLevel;
use Sif\Foundation\Security\Authentication\AuthenticationMethod;
use Sif\Foundation\Security\Context\SecurityContext;
use Sif\Foundation\Security\Contracts\TotpSecretGeneratorInterface;
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
use Sif\Foundation\Security\MultiFactor\Totp\TotpEnrollmentService;
use Sif\Foundation\Security\MultiFactor\Totp\TotpFactorId;
use Sif\Foundation\Security\MultiFactor\Totp\TotpFactorVerifier;
use Sif\Foundation\Security\MultiFactor\Totp\TotpMultiFactorService;
use Sif\Foundation\Security\MultiFactor\Totp\TotpSecret;
use Sif\Foundation\Security\Session\SessionAuthenticationManager;
use Sif\Foundation\Session\SessionId;
use Sif\Foundation\Session\SessionState;

final class MultiFactorAuthenticationProductCompletionTest extends TestCase
{
    public function testTotpEnrollmentChallengeAndSessionElevationCompleteTheProductFlow(): void
    {
        $enrolledAt = new DateTimeImmutable('2026-08-06T22:00:00+00:00');
        $challengeAt = $enrolledAt->modify('+30 seconds');
        $principal = $this->principal($enrolledAt);
        $factorStore = new InMemoryTotpFactorStore();
        $codeGenerator = new NativeTotpCodeGenerator();
        $verifier = new NativeTotpVerifier($codeGenerator);

        $enrollment = new TotpEnrollmentService(
            new ProductCompletionFixedTotpSecretGenerator(),
            $verifier,
            $factorStore
        );

        $result = $enrollment->begin(
            new TotpFactorId('factor-product-completion'),
            $principal->identity()->id(),
            $enrolledAt
        );

        $activationCode = $codeGenerator->generate(
            $result->secret(),
            $result->parameters(),
            $enrolledAt
        );

        self::assertTrue(
            $enrollment->activate(
                $result->factorId(),
                $activationCode,
                $enrolledAt
            )->isVerified()
        );

        $challengeStore = new InMemoryMultiFactorChallengeStore();
        $totpService = new TotpMultiFactorService(
            $challengeStore,
            $factorStore,
            new TotpFactorVerifier($verifier, $factorStore)
        );
        $challenge = $totpService->issue(
            $principal,
            MultiFactorChallengePurpose::StepUp,
            new AuthenticationLevel(70),
            $challengeAt
        );
        self::assertNotNull($challenge);

        $code = $codeGenerator->generate(
            $result->secret(),
            $result->parameters(),
            $challengeAt
        );

        $context = new SecurityContext($principal);
        $session = $this->session($challengeAt);
        $elevation = new MultiFactorSessionElevationService(
            $totpService,
            new RecoveryCodeMultiFactorService(
                $challengeStore,
                new InMemoryRecoveryCodeStore()
            ),
            new SessionAuthenticationManager(),
            $context
        );

        $payload = $code->expose(
            static fn (string $value): TotpChallengeResponsePayload =>
                TotpChallengeResponsePayload::fromJson(
                    json_encode([
                        'challenge_id' => $challenge->id()->value(),
                        'code' => $value,
                    ], JSON_THROW_ON_ERROR)
                )
        );

        $satisfaction = $elevation->satisfyTotp(
            $payload,
            $session,
            $challengeAt
        );

        self::assertTrue($satisfaction->isSatisfied());
        self::assertTrue($session->has(SessionAuthenticationManager::SESSION_KEY));
        self::assertTrue($session->regenerationRequested());

        $elevatedPrincipal = $context->principal();
        self::assertInstanceOf(AuthenticatedPrincipal::class, $elevatedPrincipal);
        self::assertSame(70, $elevatedPrincipal->evidence()->level()->value());
        self::assertSame('mfa.totp', $elevatedPrincipal->evidence()->method()->value());

        self::assertFalse(
            $elevation->satisfyTotp($payload, $session, $challengeAt)->isSatisfied()
        );
    }

    public function testRecoveryCodeIsConsumedOnceAndCannotSatisfyAnotherChallenge(): void
    {
        $now = new DateTimeImmutable('2026-08-06T22:10:00+00:00');
        $principal = $this->principal($now);
        $challengeStore = new InMemoryMultiFactorChallengeStore();
        $codeStore = new InMemoryRecoveryCodeStore();
        $batch = (new RecoveryCodeEnrollmentService(
            new RecoveryCodeGenerator(2),
            $codeStore
        ))->replaceForIdentity($principal->identity()->id(), $now);

        $service = new RecoveryCodeMultiFactorService(
            $challengeStore,
            $codeStore
        );
        $firstChallenge = $service->issue(
            $principal,
            MultiFactorChallengePurpose::StepUp,
            new AuthenticationLevel(70),
            $now
        );
        self::assertNotNull($firstChallenge);

        $code = $batch->expose(
            static fn (array $codes): RecoveryCode => $codes[0]
        );
        $payload = $code->expose(
            static fn (string $value): RecoveryCodeChallengeResponsePayload =>
                RecoveryCodeChallengeResponsePayload::fromJson(
                    json_encode([
                        'challenge_id' => $firstChallenge->id()->value(),
                        'code' => $value,
                    ], JSON_THROW_ON_ERROR)
                )
        );

        $context = new SecurityContext($principal);
        $session = $this->session($now);
        $elevation = new MultiFactorSessionElevationService(
            new TotpMultiFactorService(
                $challengeStore,
                new InMemoryTotpFactorStore(),
                new TotpFactorVerifier(
                    new NativeTotpVerifier(new NativeTotpCodeGenerator()),
                    new InMemoryTotpFactorStore()
                )
            ),
            $service,
            new SessionAuthenticationManager(),
            $context
        );

        self::assertTrue(
            $elevation->satisfyRecoveryCode($payload, $session, $now)->isSatisfied()
        );

        $secondChallenge = $service->issue(
            $principal,
            MultiFactorChallengePurpose::StepUp,
            new AuthenticationLevel(70),
            $now->modify('+1 minute')
        );
        self::assertNotNull($secondChallenge);

        $replayPayload = $code->expose(
            static fn (string $value): RecoveryCodeChallengeResponsePayload =>
                RecoveryCodeChallengeResponsePayload::fromJson(
                    json_encode([
                        'challenge_id' => $secondChallenge->id()->value(),
                        'code' => $value,
                    ], JSON_THROW_ON_ERROR)
                )
        );

        self::assertFalse(
            $elevation->satisfyRecoveryCode(
                $replayPayload,
                $session,
                $now->modify('+1 minute')
            )->isSatisfied()
        );
    }

    public function testSensitiveMfaArtifactsRemainRedactedInProductSnapshots(): void
    {
        $payload = TotpChallengeResponsePayload::fromJson(
            '{"challenge_id":"challenge-product","code":"123456"}'
        );

        self::assertSame('[REDACTED]', $payload->__debugInfo()['code']);
        self::assertStringNotContainsString(
            '123456',
            json_encode($payload->__debugInfo(), JSON_THROW_ON_ERROR)
        );

        $this->expectException(\LogicException::class);
        serialize($payload);
    }

    private function principal(DateTimeImmutable $now): AuthenticatedPrincipal
    {
        return new AuthenticatedPrincipal(
            new Identity(new IdentityId('product-user')),
            new PrincipalAttributeCollection(),
            new AuthenticationEvidence(
                new AuthenticationMethod('password'),
                new AuthenticationLevel(50),
                $now->modify('-5 minutes')
            )
        );
    }

    private function session(DateTimeImmutable $now): SessionState
    {
        return new SessionState(
            new SessionId(str_repeat('c', 32)),
            [],
            $now,
            $now
        );
    }
}

final readonly class ProductCompletionFixedTotpSecretGenerator implements TotpSecretGeneratorInterface
{
    public function generate(): TotpSecret
    {
        return new TotpSecret('JBSWY3DPEHPK3PXP');
    }
}
