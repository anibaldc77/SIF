<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Recovery\Events\RecoverySecurityEvent;

interface RecoverySecurityEventHandlerInterface
{
    public function handle(RecoverySecurityEvent $event): void;
}
