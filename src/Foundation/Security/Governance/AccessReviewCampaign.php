<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Governance;

use DateTimeImmutable;
use InvalidArgumentException;

final readonly class AccessReviewCampaign
{
    public function __construct(
        private AccessReviewCampaignId $id,
        private string $name,
        private AccessReviewCampaignStatus $status,
        private AccessReviewScope $scope,
        private DateTimeImmutable $startsAt,
        private DateTimeImmutable $endsAt
    ) {
        if ($this->endsAt <= $this->startsAt) {
            throw new InvalidArgumentException(
                'Access review campaign end must be after start.'
            );
        }
    }

    public function id(): AccessReviewCampaignId
    {
        return $this->id;
    }

    public function name(): string
    {
        return $this->name;
    }

    public function status(): AccessReviewCampaignStatus
    {
        return $this->status;
    }

    public function scope(): AccessReviewScope
    {
        return $this->scope;
    }

    public function startsAt(): DateTimeImmutable
    {
        return $this->startsAt;
    }

    public function endsAt(): DateTimeImmutable
    {
        return $this->endsAt;
    }

    public function activeAt(DateTimeImmutable $instant): bool
    {
        return $this->status->value()
            === AccessReviewCampaignStatus::ACTIVE
            && $instant >= $this->startsAt
            && $instant < $this->endsAt;
    }
}
