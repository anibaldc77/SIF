<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Governance;

use DateTimeImmutable;
use Sif\Foundation\Security\Contracts\AccessAssignmentProviderInterface;
use Sif\Foundation\Security\Contracts\EffectiveAccessAssignmentResolverInterface;
use Sif\Foundation\Security\Contracts\EntitlementCatalogInterface;

final readonly class DefaultEffectiveAccessAssignmentResolver implements EffectiveAccessAssignmentResolverInterface
{
    public function __construct(
        private AccessAssignmentProviderInterface $assignments,
        private EntitlementCatalogInterface $catalog
    ) {
    }

    public function resolve(
        GovernanceSubjectId $subjectId,
        DateTimeImmutable $at
    ): array {
        $resolved = [];

        foreach ($this->assignments->forSubject($subjectId) as $assignment) {
            if (!$assignment->activeAt($at)) {
                continue;
            }

            $entitlement = $this->catalog->find(
                $assignment->entitlementId()
            );

            if ($entitlement === null) {
                continue;
            }

            $resolved[] = new EffectiveAccessAssignment(
                $assignment,
                $entitlement
            );
        }

        return $resolved;
    }
}
