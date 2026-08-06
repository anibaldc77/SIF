<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Contracts\IdentityInterface;
use Sif\Foundation\Security\Contracts\IdentityProviderInterface;
use Sif\Foundation\Security\Contracts\IdentityVerificationActivatorInterface;
use Sif\Foundation\Security\Contracts\RecoveryChallengeDeliveryInterface;
use Sif\Foundation\Security\Identity\Identity;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\IdentityProvider\IdentityAccountStatus;
use Sif\Foundation\Security\IdentityProvider\IdentityLookupKey;
use Sif\Foundation\Security\IdentityProvider\IdentityProviderId;
use Sif\Foundation\Security\IdentityProvider\IdentityProviderRecord;
use Sif\Foundation\Security\IdentityProvider\IdentityProviderResult;
use Sif\Foundation\Security\Recovery\CryptographicRecoveryTokenGenerator;
use Sif\Foundation\Security\Recovery\InMemoryRecoveryChallengeStore;
use Sif\Foundation\Security\Recovery\IdentityVerification\IdentityVerificationService;
use Sif\Foundation\Security\Recovery\RecoveryChallenge;
use Sif\Foundation\Security\Recovery\RecoveryChallengePurpose;
use Sif\Foundation\Security\Recovery\RecoveryToken;

final class IdentityVerificationWorkflowTest extends TestCase
{
    public function testRequestIsGenericAndIssuesChallengeForActiveIdentity(): void
    {
        $delivery = new IdentityVerificationCapturingDelivery();
        $service = $this->service($delivery, new IdentityVerificationCapturingActivator());

        $result = $service->request(new IdentityLookupKey('user@example.test'), new DateTimeImmutable('2026-08-06T12:00:00Z'));

        self::assertInstanceOf(\Sif\Foundation\Security\Recovery\IdentityVerification\IdentityVerificationRequestResult::class, $result);
        self::assertNotNull($delivery->challenge);
        self::assertSame(RecoveryChallengePurpose::IdentityVerification, $delivery->challenge->purpose());
    }

    public function testValidTokenMarksIdentityAsVerifiedAndCannotBeReused(): void
    {
        $delivery = new IdentityVerificationCapturingDelivery();
        $activator = new IdentityVerificationCapturingActivator();
        $service = $this->service($delivery, $activator);
        $instant = new DateTimeImmutable('2026-08-06T12:00:00Z');

        $service->request(new IdentityLookupKey('user@example.test'), $instant);
        self::assertNotNull($delivery->challenge);
        self::assertNotNull($delivery->token);

        $first = $service->confirm($delivery->challenge->id(), $delivery->token, $instant->modify('+1 minute'));
        $second = $service->confirm($delivery->challenge->id(), $delivery->token, $instant->modify('+2 minutes'));

        self::assertTrue($first->isSucceeded());
        self::assertFalse($second->isSucceeded());
        self::assertSame('identity-1', $activator->verifiedIdentityId);
    }

    public function testPasswordResetChallengeCannotVerifyIdentity(): void
    {
        $delivery = new IdentityVerificationCapturingDelivery();
        $activator = new IdentityVerificationCapturingActivator();
        $store = new InMemoryRecoveryChallengeStore();
        $service = new IdentityVerificationService(
            new IdentityVerificationFixedProvider(),
            new CryptographicRecoveryTokenGenerator(),
            $store,
            $delivery,
            $activator
        );
        $instant = new DateTimeImmutable('2026-08-06T12:00:00Z');
        $token = new RecoveryToken(str_repeat('A', 43));
        $challenge = new RecoveryChallenge(
            new \Sif\Foundation\Security\Recovery\RecoveryChallengeId('password-reset-cross-purpose'),
            RecoveryChallengePurpose::PasswordReset,
            new \Sif\Foundation\Security\Recovery\RecoverySubjectKey('identity-1'),
            $instant,
            $instant->modify('+10 minutes')
        );
        $store->issue(new \Sif\Foundation\Security\Recovery\RecoveryChallengeRecord(
            $challenge,
            \Sif\Foundation\Security\Recovery\RecoveryTokenDigest::fromToken($token)
        ));

        $result = $service->confirm($challenge->id(), $token, $instant->modify('+1 minute'));

        self::assertFalse($result->isSucceeded());
        self::assertNull($activator->verifiedIdentityId);
    }

    public function testUnknownIdentityProducesNoDelivery(): void
    {
        $delivery = new IdentityVerificationCapturingDelivery();
        $service = new IdentityVerificationService(
            new IdentityVerificationMissingProvider(),
            new CryptographicRecoveryTokenGenerator(),
            new InMemoryRecoveryChallengeStore(),
            $delivery,
            new IdentityVerificationCapturingActivator()
        );

        $service->request(new IdentityLookupKey('missing@example.test'), new DateTimeImmutable('2026-08-06T12:00:00Z'));

        self::assertNull($delivery->challenge);
        self::assertNull($delivery->token);
    }

    private function service(
        IdentityVerificationCapturingDelivery $delivery,
        IdentityVerificationCapturingActivator $activator
    ): IdentityVerificationService {
        return new IdentityVerificationService(
            new IdentityVerificationFixedProvider(),
            new CryptographicRecoveryTokenGenerator(),
            new InMemoryRecoveryChallengeStore(),
            $delivery,
            $activator
        );
    }
}

final class IdentityVerificationFixedProvider implements IdentityProviderInterface
{
    public function id(): IdentityProviderId
    {
        return new IdentityProviderId('identity-verification-test');
    }

    public function resolve(IdentityLookupKey $lookupKey): IdentityProviderResult
    {
        return IdentityProviderResult::found(new IdentityProviderRecord(
            new Identity(new IdentityId('identity-1')),
            IdentityAccountStatus::Active
        ));
    }
}

final class IdentityVerificationMissingProvider implements IdentityProviderInterface
{
    public function id(): IdentityProviderId
    {
        return new IdentityProviderId('identity-verification-missing');
    }

    public function resolve(IdentityLookupKey $lookupKey): IdentityProviderResult
    {
        return IdentityProviderResult::notFound();
    }
}

final class IdentityVerificationCapturingDelivery implements RecoveryChallengeDeliveryInterface
{
    public ?RecoveryChallenge $challenge = null;
    public ?RecoveryToken $token = null;

    public function deliver(IdentityInterface $identity, RecoveryChallenge $challenge, RecoveryToken $token): void
    {
        $this->challenge = $challenge;
        $this->token = $token;
    }
}

final class IdentityVerificationCapturingActivator implements IdentityVerificationActivatorInterface
{
    public ?string $verifiedIdentityId = null;

    public function markVerified(IdentityInterface $identity): void
    {
        $this->verifiedIdentityId = $identity->id()->value();
    }
}
