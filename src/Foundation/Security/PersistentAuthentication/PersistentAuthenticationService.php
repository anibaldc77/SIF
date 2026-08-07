<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\PersistentAuthentication;

use DateTimeImmutable;
use Sif\Foundation\Security\Contracts\PersistentAuthenticationCredentialLifecycleStoreInterface;
use Sif\Foundation\Security\Contracts\PersistentAuthenticationTokenGeneratorInterface;
use Sif\Foundation\Security\Identity\IdentityId;

final readonly class PersistentAuthenticationService
{
    public function __construct(
        private PersistentAuthenticationCredentialLifecycleStoreInterface $store,
        private PersistentAuthenticationTokenGeneratorInterface $tokenGenerator
    ) {
    }

    public function issue(
        IdentityId $identityId,
        DateTimeImmutable $issuedAt,
        DateTimeImmutable $absoluteExpiresAt
    ): PersistentAuthenticationToken {
        $token = $this->tokenGenerator->generate();

        $this->store->save(
            new PersistentAuthenticationCredential(
                $token->selector(),
                $identityId,
                $token->validatorDigest(),
                $issuedAt,
                $absoluteExpiresAt
            )
        );

        return $token;
    }

    public function validateAndRotate(
        PersistentAuthenticationToken $presented,
        DateTimeImmutable $now
    ): PersistentAuthenticationValidationResult {
        $credential = $this->store->findBySelector($presented->selector());

        if ($credential === null) {
            return PersistentAuthenticationValidationResult::rejected(
                PersistentAuthenticationValidationStatus::Missing
            );
        }

        if (
            $credential->status() ===
            PersistentAuthenticationCredentialStatus::Revoked
        ) {
            return PersistentAuthenticationValidationResult::rejected(
                PersistentAuthenticationValidationStatus::Revoked
            );
        }

        if ($credential->isExpiredAt($now)) {
            $this->store->revoke($credential->selector(), $now);

            return PersistentAuthenticationValidationResult::rejected(
                PersistentAuthenticationValidationStatus::Expired
            );
        }

        if (
            !$credential->validatorDigest()->equals(
                $presented->validatorDigest()
            )
        ) {
            $this->store->revoke($credential->selector(), $now);

            return PersistentAuthenticationValidationResult::rejected(
                PersistentAuthenticationValidationStatus::ReplaySuspected
            );
        }

        $replacement = $this->tokenGenerator->generate();

        $replacementToken = new PersistentAuthenticationToken(
            $credential->selector(),
            $replacement->validator()
        );

        $replacementCredential = new PersistentAuthenticationCredential(
            $credential->selector(),
            $credential->identityId(),
            $replacementToken->validatorDigest(),
            $now,
            $credential->absoluteExpiresAt()
        );

        if (
            !$this->store->rotate(
                $credential->selector(),
                $credential->validatorDigest(),
                $replacementCredential
            )
        ) {
            return PersistentAuthenticationValidationResult::rejected(
                PersistentAuthenticationValidationStatus::ReplaySuspected
            );
        }

        return PersistentAuthenticationValidationResult::accepted(
            $replacementToken
        );
    }

    public function revoke(
        PersistentAuthenticationSelector $selector,
        DateTimeImmutable $at
    ): bool {
        return $this->store->revoke($selector, $at);
    }
}
