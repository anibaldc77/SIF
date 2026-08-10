<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\SharedSignals\CaepEvaluationResult;
use Sif\Foundation\Security\SharedSignals\ContinuousAccessContext;
use Sif\Foundation\Security\SharedSignals\ContinuousAccessDecision;
use Sif\Foundation\Security\SharedSignals\RiscEvaluationResult;

interface ContinuousAccessDecisionPolicyInterface
{
    public function decide(
        ContinuousAccessContext $context,
        ?CaepEvaluationResult $caep = null,
        ?RiscEvaluationResult $risc = null
    ): ContinuousAccessDecision;
}
