<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\SharedSignals\RiscAccountSecurityEvent;
use Sif\Foundation\Security\SharedSignals\SecurityEvent;

interface RiscSecurityEventMapperInterface
{
    public function map(
        SecurityEvent $event
    ): RiscAccountSecurityEvent;
}
