<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Recovery\IdentityVerification;

final readonly class IdentityVerificationConfirmationResult
{
    private function __construct(private IdentityVerificationConfirmationStatus $status)
    {
    }

    public static function succeeded(): self
    {
        return new self(IdentityVerificationConfirmationStatus::Succeeded);
    }

    public static function rejected(): self
    {
        return new self(IdentityVerificationConfirmationStatus::Rejected);
    }

    public function status(): IdentityVerificationConfirmationStatus
    {
        return $this->status;
    }

    public function isSucceeded(): bool
    {
        return $this->status === IdentityVerificationConfirmationStatus::Succeeded;
    }
}
