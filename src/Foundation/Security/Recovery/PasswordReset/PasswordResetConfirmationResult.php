<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Recovery\PasswordReset;

final readonly class PasswordResetConfirmationResult
{
    private function __construct(private PasswordResetConfirmationStatus $status)
    {
    }

    public static function succeeded(): self
    {
        return new self(PasswordResetConfirmationStatus::Succeeded);
    }

    public static function rejected(): self
    {
        return new self(PasswordResetConfirmationStatus::Rejected);
    }

    public function status(): PasswordResetConfirmationStatus
    {
        return $this->status;
    }

    public function isSucceeded(): bool
    {
        return $this->status === PasswordResetConfirmationStatus::Succeeded;
    }
}
