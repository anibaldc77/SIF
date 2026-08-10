<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\SharedSignals\CaepSessionEvent;
use Sif\Foundation\Security\SharedSignals\SecurityEvent;

interface CaepSecurityEventMapperInterface
{
    public function map(
        SecurityEvent $event
    ): CaepSessionEvent;
}
