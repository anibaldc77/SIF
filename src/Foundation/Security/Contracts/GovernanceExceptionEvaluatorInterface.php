<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use DateTimeImmutable;
use Sif\Foundation\Security\Governance\GovernanceException;

interface GovernanceExceptionEvaluatorInterface
{
    public function isEffective(
        GovernanceException $exception,
        DateTimeImmutable $at
    ): bool;
}
