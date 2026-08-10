<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\SharedSignals\SecurityEvent;

interface ContinuousAccessEvaluationHandlerInterface
{
    public function handle(SecurityEvent $event): void;
}
