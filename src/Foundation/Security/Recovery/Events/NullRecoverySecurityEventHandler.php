<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Recovery\Events;

use Sif\Foundation\Security\Contracts\RecoverySecurityEventHandlerInterface;

final readonly class NullRecoverySecurityEventHandler implements RecoverySecurityEventHandlerInterface
{
    public function handle(RecoverySecurityEvent $event): void
    {
    }
}
