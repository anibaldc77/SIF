<?php

declare(strict_types=1);

namespace Sif\Foundation\Tests\Fixtures\Persistence;

use Sif\Foundation\Contracts\PersistenceCapabilityProviderInterface;
use Sif\Foundation\Persistence\PersistenceCapabilities;

final readonly class CapabilityAwarePersistenceAdapter implements
    PersistenceCapabilityProviderInterface
{
    public function __construct(
        private PersistenceCapabilities $capabilities,
    ) {
    }

    public function capabilities(): PersistenceCapabilities
    {
        return $this->capabilities;
    }
}
