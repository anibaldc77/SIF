<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\SharedSignals\SharedSignalsProductProfile;
use Sif\Foundation\Security\SharedSignals\SharedSignalsProductReadinessReport;

interface SharedSignalsProductReadinessEvaluatorInterface
{
    public function evaluate(
        SharedSignalsProductProfile $profile
    ): SharedSignalsProductReadinessReport;
}
