<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\PersistentAuthentication;

final readonly class PersistentAuthenticationValidationResult
{
    private function __construct(
        private PersistentAuthenticationValidationStatus $status,
        private ?PersistentAuthenticationToken $replacementToken = null
    ) {
    }

    public static function accepted(
        PersistentAuthenticationToken $replacementToken
    ): self {
        return new self(
            PersistentAuthenticationValidationStatus::Accepted,
            $replacementToken
        );
    }

    public static function rejected(
        PersistentAuthenticationValidationStatus $status
    ): self {
        if ($status === PersistentAuthenticationValidationStatus::Accepted) {
            throw new \InvalidArgumentException(
                'Accepted validation requires a replacement token.'
            );
        }

        return new self($status);
    }

    public function status(): PersistentAuthenticationValidationStatus
    {
        return $this->status;
    }

    public function isAccepted(): bool
    {
        return $this->status === PersistentAuthenticationValidationStatus::Accepted;
    }

    public function replacementToken(): ?PersistentAuthenticationToken
    {
        return $this->replacementToken;
    }
}
