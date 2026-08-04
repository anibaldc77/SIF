<?php

declare(strict_types=1);

namespace Sif\Foundation\Session\Policy;

use DateTimeImmutable;
use Sif\Foundation\Session\SessionPolicy;
use Sif\Foundation\Session\SessionRecord;

final readonly class SessionExpirationPolicy
{
    public function __construct(private SessionPolicy $policy = new SessionPolicy())
    {
    }

    public function expired(SessionRecord $record, DateTimeImmutable $now): bool
    {
        return $record->expiredAt($now, $this->policy);
    }
}
