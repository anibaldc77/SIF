<?php

declare(strict_types=1);

namespace Sif\Foundation\Persistence;

use Sif\Foundation\Contracts\PersistenceCapabilityProviderInterface;
use Sif\Foundation\Exceptions\UnsupportedPersistenceCapabilityException;

final readonly class PersistenceCapabilityGuard
{
    public function require(
        PersistenceCapabilityProviderInterface $provider,
        PersistenceCapability $capability,
    ): void {
        if ($provider->capabilities()->supports($capability)) {
            return;
        }

        throw new UnsupportedPersistenceCapabilityException(
            capability: $capability,
            providerType: $provider::class,
        );
    }
}
