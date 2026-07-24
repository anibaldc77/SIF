<?php

declare(strict_types=1);

namespace Sif\Foundation\Events;

use DateTimeImmutable;
use JsonSerializable;
use Sif\Foundation\BootResult;
use Sif\Foundation\Contracts\ApplicationInterface;
use Sif\Foundation\Event\Observation\RuntimeOperation;

/** Immutable snapshot emitted after a delegated runtime operation returns. */
final readonly class RuntimeOperationCompleted implements JsonSerializable
{
    public function __construct(
        private ApplicationInterface $application,
        private RuntimeOperation $operation,
        private BootResult $result,
        private DateTimeImmutable $timestamp,
    ) {
    }

    public function application(): ApplicationInterface
    {
        return $this->application;
    }

    public function operation(): RuntimeOperation
    {
        return $this->operation;
    }

    public function result(): BootResult
    {
        return $this->result;
    }

    public function timestamp(): DateTimeImmutable
    {
        return $this->timestamp;
    }

    /**
     * @return array{
     *     event: string,
     *     operation: string,
     *     succeeded: bool,
     *     state: string,
     *     stage: string,
     *     timestamp: string
     * }
     */
    public function jsonSerialize(): array
    {
        return [
            'event' => 'runtime.operation.completed',
            'operation' => $this->operation->value,
            'succeeded' => $this->result->succeeded(),
            'state' => $this->application->runtime()->state()->value,
            'stage' => $this->result->stage()->value,
            'timestamp' => $this->timestamp->format(DATE_ATOM),
        ];
    }
}
