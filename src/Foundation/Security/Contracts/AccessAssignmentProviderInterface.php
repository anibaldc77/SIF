<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Contracts;

use Sif\Foundation\Security\Governance\AccessAssignment;
use Sif\Foundation\Security\Governance\GovernanceSubjectId;

interface AccessAssignmentProviderInterface
{
    /**
     * @return list<AccessAssignment>
     */
    public function forSubject(
        GovernanceSubjectId $subjectId
    ): array;
}
