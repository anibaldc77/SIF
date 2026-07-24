<?php

declare(strict_types=1);

namespace Sif\Foundation\Environment\Events;

use DateTimeImmutable;
use Sif\Foundation\Environment\Contracts\EnvironmentProviderInterface;

final readonly class EnvironmentLoading
{
    public function __construct(
        private EnvironmentProviderInterface $environment,
        private DateTimeImmutable $occurredAt = new DateTimeImmutable(),
    ) {
    }

    public function environment(): EnvironmentProviderInterface
    {
        return $this->environment;
    }

    public function occurredAt(): DateTimeImmutable
    {
        return $this->occurredAt;
    }
}
