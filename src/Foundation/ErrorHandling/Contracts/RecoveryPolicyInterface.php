<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Contracts;

use Sif\Foundation\ErrorHandling\Classification\ThrowableClassification;
use Sif\Foundation\ErrorHandling\Recovery\RecoveryDecision;

interface RecoveryPolicyInterface
{
    public function name(): string;

    /** Attempt numbers are one-based. */
    public function decide(ThrowableClassification $classification, int $attempt): ?RecoveryDecision;
}
