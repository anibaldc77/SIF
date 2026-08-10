<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\SharedSignals\ContinuousAccessContext;
use Sif\Foundation\Security\SharedSignals\ContinuousAccessDecision;
use Sif\Foundation\Security\SharedSignals\ContinuousAccessExecutionResult;

interface ContinuousAccessReactionExecutorInterface
{
    public function execute(
        ContinuousAccessContext $context,
        ContinuousAccessDecision $decision
    ): ContinuousAccessExecutionResult;
}
