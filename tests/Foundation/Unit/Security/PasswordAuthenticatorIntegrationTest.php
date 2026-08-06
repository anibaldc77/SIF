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
use Sif\Foundation\Security\Contracts\PasswordRehashRequiredHandlerInterface;
use Sif\Foundation\Security\Contracts\PasswordVerifierInterface;
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
use Sif\Foundation\Security\Password\StoredPasswordHash;
use Sif\Foundation\Security\Results\AuthenticationFailureReason;
use Sif\Foundation\Security\Results\AuthenticationResult;

final class PasswordAuthenticatorIntegrationTest extends TestCase
{
    public function testAuthenticatesActiveIdentityAndBuildsPasswordEvidence(): void
    {
        $identity = new Identity(new IdentityId('user-100'));
        $authenticator = $this->authenticator(
            IdentityProviderResult::found(new IdentityProviderRecord($identity, IdentityAccountStatus::Active)),
            PasswordHashProviderResult::found($this->hash('real-hash')),
            PasswordVerificationResult::verified()
        );

        $result = $authenticator->authenticate($this->request());

        self::assertTrue($result->isSuccessful());
        $principal = $result->principal();
        self::assertNotNull($principal);
        self::assertSame('user-100', $principal->identity()->id()->value());
        self::assertSame('password', $principal->evidence()->method()->value());
        self::assertSame(20, $principal->evidence()->level()->value());
    }

    public function testUnknownIdentityAndInvalidPasswordUseSamePublicFailure(): void
    {
        $unknownVerifier = new RecordingPasswordVerifier(PasswordVerificationResult::rejected());
        $unknown = $this->authenticator(
            IdentityProviderResult::notFound(),
            PasswordHashProviderResult::notFound(),
            PasswordVerificationResult::rejected(),
            $unknownVerifier
        )->authenticate($this->request());

        $known = $this->authenticator(
            IdentityProviderResult::found(new IdentityProviderRecord(
                new Identity(new IdentityId('user-100')),
                IdentityAccountStatus::Active
            )),
            PasswordHashProviderResult::found($this->hash('real-hash')),
            PasswordVerificationResult::rejected()
        )->authenticate($this->request());

        self::assertSame(AuthenticationFailureReason::InvalidCredentials, $unknown->failure()?->reason());
        self::assertSame(AuthenticationFailureReason::InvalidCredentials, $known->failure()?->reason());
        self::assertSame('fallback-hash', $unknownVerifier->lastHashMarker);
    }

    public function testMissingStoredHashFailsClosedAndUsesFallbackVerification(): void
    {
        $verifier = new RecordingPasswordVerifier(PasswordVerificationResult::verified());
        $result = $this->authenticator(
            IdentityProviderResult::found(new IdentityProviderRecord(
                new Identity(new IdentityId('user-100')),
                IdentityAccountStatus::Active
            )),
            PasswordHashProviderResult::notFound(),
            PasswordVerificationResult::verified(),
            $verifier
        )->authenticate($this->request());

        self::assertSame(AuthenticationFailureReason::InvalidCredentials, $result->failure()?->reason());
        self::assertSame('fallback-hash', $verifier->lastHashMarker);
    }

    public function testDisabledAndLockedAccountsAreRejectedAfterVerification(): void
    {
        foreach ([IdentityAccountStatus::Disabled, IdentityAccountStatus::Locked] as $status) {
            $result = $this->authenticator(
                IdentityProviderResult::found(new IdentityProviderRecord(
                    new Identity(new IdentityId('user-' . $status->value)),
                    $status
                )),
                PasswordHashProviderResult::found($this->hash('real-hash')),
                PasswordVerificationResult::verified()
            )->authenticate($this->request());

            self::assertSame(AuthenticationFailureReason::Rejected, $result->failure()?->reason());
        }
    }

    public function testRehashRequirementIsSignalledOnlyAfterSuccessfulAuthentication(): void
    {
        $identity = new Identity(new IdentityId('user-100'));
        $handler = new RecordingRehashHandler();
        $authenticator = $this->authenticator(
            IdentityProviderResult::found(new IdentityProviderRecord($identity, IdentityAccountStatus::Active)),
            PasswordHashProviderResult::found($this->hash('real-hash')),
            PasswordVerificationResult::verified(true),
            null,
            $handler
        );

        self::assertTrue($authenticator->authenticate($this->request())->isSuccessful());
        self::assertSame('user-100', $handler->identityId);
        self::assertSame('real-hash', $handler->hashMarker);
    }

    public function testCredentialSnapshotIsRedactedAndCannotBeSerialized(): void
    {
        $credential = new PasswordAuthenticationCredential(
            new IdentityLookupKey('alice'),
            new PasswordCredential(new PasswordSecret('secret-value'))
        );

        self::assertSame(['lookup_key' => 'alice', 'password' => '[REDACTED]'], $credential->__debugInfo());

        $this->expectException(\LogicException::class);
        serialize($credential);
    }

    private function authenticator(
        IdentityProviderResult $identityResult,
        PasswordHashProviderResult $hashResult,
        PasswordVerificationResult $verificationResult,
        ?RecordingPasswordVerifier $verifier = null,
        ?PasswordRehashRequiredHandlerInterface $handler = null
    ): PasswordAuthenticator {
        return new PasswordAuthenticator(
            new FixedIdentityProvider($identityResult),
            new FixedPasswordHashProvider($hashResult),
            $verifier ?? new RecordingPasswordVerifier($verificationResult),
            $this->hash('fallback-hash'),
            $handler
        );
    }

    private function request(): AuthenticationRequest
    {
        return new AuthenticationRequest(
            new AuthenticationRequestId('password-request-1'),
            new PasswordAuthenticationCredential(
                new IdentityLookupKey('alice'),
                new PasswordCredential(new PasswordSecret('correct horse battery staple'))
            ),
            new DateTimeImmutable('2026-08-06T12:00:00+00:00')
        );
    }

    private function hash(string $marker): StoredPasswordHash
    {
        return new StoredPasswordHash(new PasswordHashAlgorithm('test'), $marker);
    }
}

final readonly class FixedIdentityProvider implements IdentityProviderInterface
{
    public function __construct(private IdentityProviderResult $result)
    {
    }

    public function id(): IdentityProviderId
    {
        return new IdentityProviderId('test-provider');
    }

    public function resolve(IdentityLookupKey $lookupKey): IdentityProviderResult
    {
        return $this->result;
    }
}

final readonly class FixedPasswordHashProvider implements PasswordHashProviderInterface
{
    public function __construct(private PasswordHashProviderResult $result)
    {
    }

    public function findFor(IdentityInterface $identity): PasswordHashProviderResult
    {
        return $this->result;
    }
}

final class RecordingPasswordVerifier implements PasswordVerifierInterface
{
    public ?string $lastHashMarker = null;

    public function __construct(private readonly PasswordVerificationResult $result)
    {
    }

    public function verify(
        PasswordCredential $credential,
        StoredPasswordHash $storedHash
    ): PasswordVerificationResult {
        $this->lastHashMarker = $storedHash->exposeEncodedHash(
            static fn (string $hash): string => $hash
        );

        return $this->result;
    }
}

final class RecordingRehashHandler implements PasswordRehashRequiredHandlerInterface
{
    public ?string $identityId = null;

    public ?string $hashMarker = null;

    public function handle(IdentityInterface $identity, StoredPasswordHash $currentHash): void
    {
        $this->identityId = $identity->id()->value();
        $this->hashMarker = $currentHash->exposeEncodedHash(
            static fn (string $hash): string => $hash
        );
    }
}
