<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Recovery\Protection;

use DateTimeImmutable;
use Sif\Foundation\Security\Contracts\RecoveryRequestProtectorInterface;

final readonly class NullRecoveryRequestProtector implements RecoveryRequestProtectorInterface
{
    public function assess(RecoveryRequestKey $key, DateTimeImmutable $instant): RecoveryRequestDecision
    {
        return RecoveryRequestDecision::allow();
    }

    public function record(RecoveryRequestKey $key, DateTimeImmutable $instant): void
    {
    }
}
