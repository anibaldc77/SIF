<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use DateTimeImmutable;
use Sif\Foundation\Security\Recovery\Protection\RecoveryRequestDecision;
use Sif\Foundation\Security\Recovery\Protection\RecoveryRequestKey;

interface RecoveryRequestProtectorInterface
{
    public function assess(RecoveryRequestKey $key, DateTimeImmutable $instant): RecoveryRequestDecision;

    public function record(RecoveryRequestKey $key, DateTimeImmutable $instant): void;
}
