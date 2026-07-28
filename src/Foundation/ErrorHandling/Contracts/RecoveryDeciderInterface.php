<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Contracts;

use Sif\Foundation\ErrorHandling\Classification\ThrowableClassification;
use Sif\Foundation\ErrorHandling\Recovery\RecoveryDecision;

interface RecoveryDeciderInterface
{
    public function decide(ThrowableClassification $classification, int $attempt = 1): RecoveryDecision;
}
