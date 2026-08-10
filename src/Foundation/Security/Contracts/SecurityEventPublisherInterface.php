<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\SharedSignals\SecurityEvent;

interface SecurityEventPublisherInterface
{
    public function publish(SecurityEvent $event): void;
}
