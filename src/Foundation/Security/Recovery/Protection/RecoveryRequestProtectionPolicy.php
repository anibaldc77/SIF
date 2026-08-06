<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Recovery\Protection;

use DateInterval;
use InvalidArgumentException;

final readonly class RecoveryRequestProtectionPolicy
{
    public function __construct(
        private int $maximumRequests = 3,
        private DateInterval $window = new DateInterval('PT15M'),
        private DateInterval $blockDuration = new DateInterval('PT30M')
    ) {
        if ($maximumRequests < 1) {
            throw new InvalidArgumentException('Maximum recovery requests must be positive.');
        }
    }

    public function maximumRequests(): int
    {
        return $this->maximumRequests;
    }

    public function window(): DateInterval
    {
        return $this->window;
    }

    public function blockDuration(): DateInterval
    {
        return $this->blockDuration;
    }
}
