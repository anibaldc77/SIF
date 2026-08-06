<?php

declare(strict_types=1);

namespace Sif\Tests\Foundation\Unit\Security;

use DateTimeImmutable;
use PHPUnit\Framework\TestCase;
use Sif\Foundation\Security\Authentication\AuthenticationRequest;
use Sif\Foundation\Security\Authentication\AuthenticationRequestId;
use Sif\Foundation\Security\Contracts\IdentityInterface;
use Sif\Foundation\Security\Contracts\IdentityProviderInterface;
use Sif\Foundation\Security\Contracts\PasswordHashProviderInterface;
use Sif\Foundation\Security\Contracts\PasswordVerifierInterface;
use Sif\Foundation\Security\Exceptions\InvalidPasswordAttemptProtectionException;
use Sif\Foundation\Security\Identity\Identity;
use Sif\Foundation\Security\Identity\IdentityId;
use Sif\Foundation\Security\IdentityProvider\IdentityAccountStatus;
use Sif\Foundation\Security\IdentityProvider\IdentityLookupKey;
use Sif\Foundation\Security\IdentityProvider\IdentityProviderId;
use Sif\Foundation\Security\IdentityProvider\IdentityProviderRecord;
use Sif\Foundation\Security\IdentityProvider\IdentityProviderResult;
use Sif\Foundation\Security\Password\Authentication\PasswordAuthenticationCredential;
use Sif\Foundation\Security\Password\Authentication\PasswordAuthenticator;
use Sif\Foundation\Security\Password\Authentication\PasswordHashProviderResult;
use Sif\Foundation\Security\Password\PasswordCredential;
use Sif\Foundation\Security\Password\PasswordHashAlgorithm;
use Sif\Foundation\Security\Password\PasswordSecret;
use Sif\Foundation\Security\Password\PasswordVerificationResult;
use Sif\Foundation\Security\Password\Protection\InMemoryPasswordAttemptProtector;
use Sif\Foundation\Security\Password\Protection\PasswordAttemptKey;
use Sif\Foundation\Security\Password\Protection\PasswordAttemptPolicy;
use Sif\Foundation\Security\Password\StoredPasswordHash;
use Sif\Foundation\Security\Results\AuthenticationFailureReason;

final class PasswordAttemptProtectionAndTemporaryLockoutTest extends TestCase
{
    public function testPolicyRejectsInvalidThresholds(): void
    {
        $this->expectException(InvalidPasswordAttemptProtectionException::class);
        new PasswordAttemptPolicy(0, 60, 60);
    }

    public function testProtectorBlocksAfterConfiguredFailures(): void
    {
        $protector = new InMemoryPasswordAttemptProtector(new PasswordAttemptPolicy(2, 60, 120));
        $key = $this->key();
        $time = new DateTimeImmutable('2026-08-06T15:00:00+00:00');

        self::assertTrue($protector->inspect($key, $time)->isAllowed());
        $protector->recordFailure($key, $time);
        $protector->recordFailure($key, $time->modify('+1 second'));

        $decision = $protector->inspect($key, $time->modify('+2 seconds'));
        self::assertFalse($decision->isAllowed());
        self::assertSame('2026-08-06T15:02:01+00:00', $decision->retryAt()?->format('Y-m-d\TH:i:sP'));
    }

    public function testSuccessfulAttemptClearsPreviousFailures(): void
    {
        $protector = new InMemoryPasswordAttemptProtector(new PasswordAttemptPolicy(2, 60, 120));
        $key = $this->key();
        $time = new DateTimeImmutable('2026-08-06T15:00:00+00:00');

        $protector->recordFailure($key, $time);
        $protector->recordSuccess($key, $time->modify('+1 second'));
        $protector->recordFailure($key, $time->modify('+2 seconds'));

        self::assertTrue($protector->inspect($key, $time->modify('+3 seconds'))->isAllowed());
    }

    public function testFailuresExpireOutsideObservationWindow(): void
    {
        $protector = new InMemoryPasswordAttemptProtector(new PasswordAttemptPolicy(2, 10, 120));
        $key = $this->key();
        $time = new DateTimeImmutable('2026-08-06T15:00:00+00:00');

        $protector->recordFailure($key, $time);
        $protector->recordFailure($key, $time->modify('+11 seconds'));

        self::assertTrue($protector->inspect($key, $time->modify('+12 seconds'))->isAllowed());
    }

    public function testAuthenticatorStopsBeforeIdentityResolutionWhenBlocked(): void
    {
        $protector = new InMemoryPasswordAttemptProtector(new PasswordAttemptPolicy(1, 60, 120));
        $provider = new CountingIdentityProvider();
        $authenticator = $this->authenticator($provider, $protector, PasswordVerificationResult::rejected());

        $first = $authenticator->authenticate($this->request('request-1', '2026-08-06T15:00:00+00:00'));
        $second = $authenticator->authenticate($this->request('request-2', '2026-08-06T15:00:01+00:00'));

        self::assertSame(AuthenticationFailureReason::InvalidCredentials, $first->failure()?->reason());
        self::assertSame(AuthenticationFailureReason::Rejected, $second->failure()?->reason());
        self::assertSame(1, $provider->resolutionCount);
    }

    public function testSuccessfulAuthenticationResetsProtectionState(): void
    {
        $protector = new InMemoryPasswordAttemptProtector(new PasswordAttemptPolicy(2, 60, 120));
        $provider = new CountingIdentityProvider();

        $this->authenticator($provider, $protector, PasswordVerificationResult::rejected())
            ->authenticate($this->request('request-1', '2026-08-06T15:00:00+00:00'));

        $successful = $this->authenticator($provider, $protector, PasswordVerificationResult::verified())
            ->authenticate($this->request('request-2', '2026-08-06T15:00:01+00:00'));

        $next = $this->authenticator($provider, $protector, PasswordVerificationResult::rejected())
            ->authenticate($this->request('request-3', '2026-08-06T15:00:02+00:00'));

        self::assertTrue($successful->isSuccessful());
        self::assertSame(AuthenticationFailureReason::InvalidCredentials, $next->failure()?->reason());
    }

    public function testAttemptKeyDebugInformationDoesNotExposeLookupValue(): void
    {
        $key = $this->key();
        $debug = $key->__debugInfo();

        self::assertSame('local', $debug['provider_id']);
        self::assertSame(64, strlen($debug['lookup_fingerprint']));
        self::assertStringNotContainsString('alice', implode('|', $debug));
    }

    private function authenticator(
        CountingIdentityProvider $provider,
        InMemoryPasswordAttemptProtector $protector,
        PasswordVerificationResult $verification
    ): PasswordAuthenticator {
        return new PasswordAuthenticator(
            $provider,
            new FixedProtectionPasswordHashProvider(),
            new FixedProtectionPasswordVerifier($verification),
            $this->hash('fallback-hash'),
            null,
            $protector
        );
    }

    private function request(string $id, string $time): AuthenticationRequest
    {
        return new AuthenticationRequest(
            new AuthenticationRequestId($id),
            new PasswordAuthenticationCredential(
                new IdentityLookupKey('alice'),
                new PasswordCredential(new PasswordSecret('secret-value'))
            ),
            new DateTimeImmutable($time)
        );
    }

    private function key(): PasswordAttemptKey
    {
        return new PasswordAttemptKey(new IdentityProviderId('local'), new IdentityLookupKey('alice'));
    }

    private function hash(string $marker): StoredPasswordHash
    {
        return new StoredPasswordHash(new PasswordHashAlgorithm('test'), $marker);
    }
}

final class CountingIdentityProvider implements IdentityProviderInterface
{
    public int $resolutionCount = 0;

    public function id(): IdentityProviderId
    {
        return new IdentityProviderId('local');
    }

    public function resolve(IdentityLookupKey $lookupKey): IdentityProviderResult
    {
        ++$this->resolutionCount;

        return IdentityProviderResult::found(new IdentityProviderRecord(
            new Identity(new IdentityId('user-100')),
            IdentityAccountStatus::Active
        ));
    }
}

final readonly class FixedProtectionPasswordHashProvider implements PasswordHashProviderInterface
{
    public function findFor(IdentityInterface $identity): PasswordHashProviderResult
    {
        return PasswordHashProviderResult::found(
            new StoredPasswordHash(new PasswordHashAlgorithm('test'), 'stored-hash')
        );
    }
}

final readonly class FixedProtectionPasswordVerifier implements PasswordVerifierInterface
{
    public function __construct(private PasswordVerificationResult $result)
    {
    }

    public function verify(
        PasswordCredential $credential,
        StoredPasswordHash $storedHash
    ): PasswordVerificationResult {
        return $this->result;
    }
}
