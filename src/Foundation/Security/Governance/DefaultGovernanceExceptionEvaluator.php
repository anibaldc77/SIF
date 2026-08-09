<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Governance;

use DateTimeImmutable;
use Sif\Foundation\Security\Contracts\GovernanceExceptionEvaluatorInterface;

final readonly class DefaultGovernanceExceptionEvaluator implements GovernanceExceptionEvaluatorInterface
{
    public function isEffective(
        GovernanceException $exception,
        DateTimeImmutable $at
    ): bool {
        if (!$exception->activeAt($at)) {
            return false;
        }

        $acceptance = $exception->riskAcceptance();

        if ($acceptance !== null && !$acceptance->validAt($at)) {
            return false;
        }

        return true;
    }
}
