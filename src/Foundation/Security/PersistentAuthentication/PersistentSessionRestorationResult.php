<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\PersistentAuthentication;

use Sif\Foundation\Security\Identity\AuthenticatedPrincipal;

final readonly class PersistentSessionRestorationResult
{
    private function __construct(
        private PersistentSessionRestorationStatus $status,
        private ?AuthenticatedPrincipal $principal = null,
        private ?PersistentAuthenticationToken $replacementToken = null
    ) {
    }

    public static function restored(
        AuthenticatedPrincipal $principal,
        PersistentAuthenticationToken $replacementToken
    ): self {
        return new self(
            PersistentSessionRestorationStatus::Restored,
            $principal,
            $replacementToken
        );
    }

    public static function rejected(
        PersistentSessionRestorationStatus $status
    ): self {
        if ($status === PersistentSessionRestorationStatus::Restored) {
            throw new \InvalidArgumentException(
                'Restored result requires principal and replacement token.'
            );
        }

        return new self($status);
    }

    public function status(): PersistentSessionRestorationStatus
    {
        return $this->status;
    }

    public function isRestored(): bool
    {
        return $this->status === PersistentSessionRestorationStatus::Restored;
    }

    public function principal(): ?AuthenticatedPrincipal
    {
        return $this->principal;
    }

    public function replacementToken(): ?PersistentAuthenticationToken
    {
        return $this->replacementToken;
    }
}
