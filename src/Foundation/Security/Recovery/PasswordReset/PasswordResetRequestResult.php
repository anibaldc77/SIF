<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Recovery\PasswordReset;

final readonly class PasswordResetRequestResult
{
    public function isAccepted(): bool
    {
        return true;
    }

    /** @return array{accepted: true} */
    public function snapshot(): array
    {
        return ['accepted' => true];
    }
}
