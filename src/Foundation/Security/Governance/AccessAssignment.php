<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Governance;

use DateTimeImmutable;

final readonly class AccessAssignment
{
    public function __construct(
        private GovernanceSubjectId $subjectId,
        private EntitlementId $entitlementId,
        private DateTimeImmutable $assignedAt,
        private ?DateTimeImmutable $expiresAt = null
    ) {
    }

    public function subjectId(): GovernanceSubjectId
    {
        return $this->subjectId;
    }

    public function entitlementId(): EntitlementId
    {
        return $this->entitlementId;
    }

    public function assignedAt(): DateTimeImmutable
    {
        return $this->assignedAt;
    }

    public function expiresAt(): ?DateTimeImmutable
    {
        return $this->expiresAt;
    }

    public function activeAt(DateTimeImmutable $instant): bool
    {
        if ($instant < $this->assignedAt) {
            return false;
        }

        return $this->expiresAt === null
            || $instant < $this->expiresAt;
    }
}
