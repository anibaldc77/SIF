<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\SharedSignals\SecurityEvent;
use Sif\Foundation\Security\SharedSignals\SecurityEventProvisioningContext;

interface SecurityEventProvisioningMapperInterface
{
    public function map(
        SecurityEvent $event
    ): SecurityEventProvisioningContext;
}
