<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Password\Authentication;

use Sif\Foundation\Security\Authentication\AuthenticationEvidence;
use Sif\Foundation\Security\Authentication\AuthenticationLevel;
use Sif\Foundation\Security\Authentication\AuthenticationMethod;
use Sif\Foundation\Security\Authentication\AuthenticationRequest;
use Sif\Foundation\Security\Authentication\AuthenticatorId;
use Sif\Foundation\Security\Contracts\AuthenticatorInterface;
use Sif\Foundation\Security\Contracts\IdentityProviderInterface;
use Sif\Foundation\Security\Contracts\PasswordAttemptProtectorInterface;
use Sif\Foundation\Security\Contracts\PasswordHashProviderInterface;
use Sif\Foundation\Security\Contracts\PasswordRehashCoordinatorInterface;
use Sif\Foundation\Security\Contracts\PasswordRehashRequiredHandlerInterface;
use Sif\Foundation\Security\Contracts\PasswordVerifierInterface;
use Sif\Foundation\Security\Credentials\CredentialType;
use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;
use Sif\Foundation\Security\Identity\PrincipalAttributeCollection;
use Sif\Foundation\Security\IdentityProvider\IdentityAccountStatus;
use Sif\Foundation\Security\Password\Protection\NullPasswordAttemptProtector;
use Sif\Foundation\Security\Password\Rehash\NullPasswordRehashCoordinator;
use Sif\Foundation\Security\Password\Protection\PasswordAttemptKey;
use Sif\Foundation\Security\Password\StoredPasswordHash;
use Sif\Foundation\Security\Results\AuthenticationFailureReason;
use Sif\Foundation\Security\Results\AuthenticationResult;

final readonly class PasswordAuthenticator implements AuthenticatorInterface
{
    private PasswordRehashRequiredHandlerInterface $rehashHandler;

    private PasswordAttemptProtectorInterface $attemptProtector;

    private PasswordRehashCoordinatorInterface $rehashCoordinator;

    public function __construct(
        private IdentityProviderInterface $identityProvider,
        private PasswordHashProviderInterface $hashProvider,
        private PasswordVerifierInterface $verifier,
        private StoredPasswordHash $fallbackHash,
        ?PasswordRehashRequiredHandlerInterface $rehashHandler = null,
        ?PasswordAttemptProtectorInterface $attemptProtector = null,
        ?PasswordRehashCoordinatorInterface $rehashCoordinator = null
    ) {
        $this->rehashHandler = $rehashHandler ?? new NullPasswordRehashRequiredHandler();
        $this->attemptProtector = $attemptProtector ?? new NullPasswordAttemptProtector();
        $this->rehashCoordinator = $rehashCoordinator ?? new NullPasswordRehashCoordinator();
    }

    public function id(): AuthenticatorId
    {
        return new AuthenticatorId('password');
    }

    public function supportedCredentialTypes(): array
    {
        return [new CredentialType('password')];
    }

    public function authenticate(AuthenticationRequest $request): AuthenticationResult
    {
        $credential = $request->credential();

        if (!$credential instanceof PasswordAuthenticationCredential) {
            return AuthenticationResult::failed(AuthenticationFailureReason::UnsupportedCredentials);
        }

        $attemptKey = new PasswordAttemptKey($this->identityProvider->id(), $credential->lookupKey());
        $attemptedAt = $request->requestedAt();

        if (!$this->attemptProtector->inspect($attemptKey, $attemptedAt)->isAllowed()) {
            return AuthenticationResult::failed(AuthenticationFailureReason::Rejected);
        }

        $identityResult = $this->identityProvider->resolve($credential->lookupKey());

        if (!$identityResult->wasFound()) {
            $this->verifier->verify($credential->password(), $this->fallbackHash);
            $this->attemptProtector->recordFailure($attemptKey, $attemptedAt);

            return AuthenticationResult::failed(AuthenticationFailureReason::InvalidCredentials);
        }

        $record = $identityResult->record();
        $hashResult = $this->hashProvider->findFor($record->identity());
        $storedHash = $hashResult->wasFound() ? $hashResult->hash() : $this->fallbackHash;
        $verification = $this->verifier->verify($credential->password(), $storedHash);

        if (!$hashResult->wasFound() || !$verification->isVerified()) {
            $this->attemptProtector->recordFailure($attemptKey, $attemptedAt);

            return AuthenticationResult::failed(AuthenticationFailureReason::InvalidCredentials);
        }

        if ($record->status() !== IdentityAccountStatus::Active) {
            $this->attemptProtector->recordFailure($attemptKey, $attemptedAt);

            return AuthenticationResult::failed(AuthenticationFailureReason::Rejected);
        }

        $this->attemptProtector->recordSuccess($attemptKey, $attemptedAt);

        if ($verification->requiresRehash()) {
            $this->rehashHandler->handle($record->identity(), $storedHash);
            $this->rehashCoordinator->rehash($record->identity(), $credential->password(), $storedHash);
        }

        return AuthenticationResult::succeeded(new AuthenticatedPrincipal(
            $record->identity(),
            new PrincipalAttributeCollection(),
            new AuthenticationEvidence(
                new AuthenticationMethod('password'),
                new AuthenticationLevel(20),
                $request->requestedAt()
            )
        ));
    }
}
