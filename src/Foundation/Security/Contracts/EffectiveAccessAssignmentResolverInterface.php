<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use DateTimeImmutable;
use Sif\Foundation\Security\Governance\EffectiveAccessAssignment;
use Sif\Foundation\Security\Governance\GovernanceSubjectId;

interface EffectiveAccessAssignmentResolverInterface
{
    /**
     * @return list<EffectiveAccessAssignment>
     */
    public function resolve(
        GovernanceSubjectId $subjectId,
        DateTimeImmutable $at
    ): array;
}
