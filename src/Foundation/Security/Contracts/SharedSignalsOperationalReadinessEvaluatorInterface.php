<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\SharedSignals\SharedSignalsOperationalContext;
use Sif\Foundation\Security\SharedSignals\SharedSignalsOperationalReadinessReport;

interface SharedSignalsOperationalReadinessEvaluatorInterface
{
    public function evaluate(
        SharedSignalsOperationalContext $context
    ): SharedSignalsOperationalReadinessReport;
}
