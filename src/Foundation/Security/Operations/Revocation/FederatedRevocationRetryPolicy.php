<?php

declare(strict_types=1);

namespace Sif\Foundation\Security\Operations\Revocation;

use DateInterval;
use InvalidArgumentException;

final readonly class FederatedRevocationRetryPolicy
{
    public function __construct(
        private int $maxAttempts,
        private DateInterval $baseDelay
    ) {
        if ($this->maxAttempts < 1 || $this->maxAttempts > 20) {
            throw new InvalidArgumentException(
                'Federated revocation max attempts must be between 1 and 20.'
            );
        }
    }

    public function maxAttempts(): int
    {
        return $this->maxAttempts;
    }

    public function delayForAttempt(int $attempt): DateInterval
    {
        if ($attempt < 1 || $attempt > $this->maxAttempts) {
            throw new InvalidArgumentException(
                'Federated revocation retry attempt is outside policy bounds.'
            );
        }

        $seconds = $this->baseDelaySeconds()
            * (2 ** ($attempt - 1));

        return new DateInterval('PT' . $seconds . 'S');
    }

    private function baseDelaySeconds(): int
    {
        $anchor = new \DateTimeImmutable('@0');
        $delayed = $anchor->add($this->baseDelay);

        return $delayed->getTimestamp();
    }
}
