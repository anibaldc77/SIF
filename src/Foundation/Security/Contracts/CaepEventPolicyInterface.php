<?php
declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\SharedSignals\CaepEvaluationResult;
use Sif\Foundation\Security\SharedSignals\CaepSessionEvent;

interface CaepEventPolicyInterface
{
    public function evaluate(
        CaepSessionEvent $event
    ): CaepEvaluationResult;
}
