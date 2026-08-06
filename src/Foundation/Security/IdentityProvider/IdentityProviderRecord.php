<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\IdentityProvider;

use Sif\Foundation\Security\Contracts\IdentityInterface;

final readonly class IdentityProviderRecord
{
    public function __construct(
        private IdentityInterface $identity,
        private IdentityAccountStatus $status
    ) {
    }

    public function identity(): IdentityInterface
    {
        return $this->identity;
    }

    public function status(): IdentityAccountStatus
    {
        return $this->status;
    }

    public function isActive(): bool
    {
        return $this->status === IdentityAccountStatus::Active;
    }
}
