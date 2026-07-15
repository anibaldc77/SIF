<?php

declare(strict_types=1);

namespace Sif\Foundation\Events;

use DateTimeImmutable;
use JsonSerializable;
use Sif\Foundation\Contracts\ApplicationInterface;

/** Immutable snapshot indicating that framework boot is beginning. */
final readonly class FrameworkBooting implements JsonSerializable
{
    public function __construct(
        private ApplicationInterface $application,
        private DateTimeImmutable $timestamp,
    ) {
    }

    public function application(): ApplicationInterface
    {
        return $this->application;
    }

    public function timestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }

    /** @return array{event: string, environment: string, state: string, stage: string, capabilities: list<string>, timestamp: string} */
    public function jsonSerialize(): array
    {
        return ['event' => 'framework.booting', 'environment' => $this->application->environment()->name(), 'state' => $this->application->runtime()->state()->value, 'stage' => $this->application->runtime()->stage()->value, 'capabilities' => $this->application->capabilities(), 'timestamp' => $this->timestamp->format(DATE_ATOM)];
    }
}
