<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Persistence\PersistenceCapabilities;

interface PersistenceCapabilityProviderInterface
{
    public function capabilities(): PersistenceCapabilities;
}
