<?php

declare(strict_types=1);

namespace Sif\Foundation\Events;

use DateTimeImmutable;
use JsonSerializable;
use Sif\Foundation\Contracts\RuntimeInterface;

/** Immutable failure snapshot that retains but never serializes its original cause. */
final readonly class FrameworkFailed implements JsonSerializable
{
    public function __construct(private RuntimeInterface $runtime, private \Throwable $cause, private DateTimeImmutable $timestamp)
    {
    }
    public function runtime(): RuntimeInterface
    {
        return $this->runtime;
    }
    public function cause(): \Throwable
    {
        return $this->cause;
    }
    public function timestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }
    /** @return array{event: string, state: string, stage: string, timestamp: string, diagnostic: array{code: string, type: string}} */
    public function jsonSerialize(): array
    {
        return ['event' => 'framework.failed', 'state' => $this->runtime->state()->value, 'stage' => $this->runtime->stage()->value, 'timestamp' => $this->timestamp->format(DATE_ATOM), 'diagnostic' => ['code' => 'framework.failed', 'type' => $this->cause::class]];
    }
}
