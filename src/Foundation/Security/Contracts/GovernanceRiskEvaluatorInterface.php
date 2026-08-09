<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use DateTimeImmutable;
use Sif\Foundation\Security\Governance\GovernanceRiskAssessment;
use Sif\Foundation\Security\Governance\GovernanceSubjectId;

interface GovernanceRiskEvaluatorInterface
{
    public function evaluate(
        GovernanceSubjectId $subjectId,
        DateTimeImmutable $at
    ): GovernanceRiskAssessment;
}
