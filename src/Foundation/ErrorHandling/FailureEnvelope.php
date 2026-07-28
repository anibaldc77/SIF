<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling;

use Sif\Foundation\ErrorHandling\Contracts\FailureClockInterface;
use Sif\Foundation\ErrorHandling\Exceptions\InvalidFailureEnvelopeException;
use Throwable;

final readonly class FailureEnvelope
{
    /** @var array<string, null|bool|int|float|string|array<mixed>> */
    private array $metadata;

    /**
     * @param array<string, null|bool|int|float|string|array<mixed>> $metadata
     */
    public function __construct(
        private FailureId $id,
        private FailureTimestamp $occurredAt,
        private FailureCategory $category,
        private FailureSeverity $severity,
        private FailureDisposition $disposition,
        private FailureOrigin $origin,
        private Throwable $throwable,
        array $metadata = [],
    ) {
        foreach ($metadata as $key => $value) {
            if (!is_string($key) || trim($key) === '') {
                throw new InvalidFailureEnvelopeException('Failure metadata keys must be non-empty strings.');
            }
            self::assertStructuredValue($value);
        }
        $this->metadata = $metadata;
    }

    /** @param array<string, null|bool|int|float|string|array<mixed>> $metadata */
    public static function capture(
        FailureId $id,
        FailureClockInterface $clock,
        FailureCategory $category,
        FailureSeverity $severity,
        FailureDisposition $disposition,
        FailureOrigin $origin,
        Throwable $throwable,
        array $metadata = [],
    ): self {
        return new self($id, new FailureTimestamp($clock->now()), $category, $severity, $disposition, $origin, $throwable, $metadata);
    }

    public function id(): FailureId { return $this->id; }
    public function occurredAt(): FailureTimestamp { return $this->occurredAt; }
    public function category(): FailureCategory { return $this->category; }
    public function severity(): FailureSeverity { return $this->severity; }
    public function disposition(): FailureDisposition { return $this->disposition; }
    public function origin(): FailureOrigin { return $this->origin; }
    public function throwable(): Throwable { return $this->throwable; }

    /** @return array<string, null|bool|int|float|string|array<mixed>> */
    public function metadata(): array { return $this->metadata; }

    /** @return array{id:string,occurred_at:string,category:string,severity:string,disposition:string,origin:string,throwable:array{type:string,message:string,code:int|string},metadata:array<string, null|bool|int|float|string|array<mixed>>} */
    public function summary(): array
    {
        return [
            'id' => $this->id->value(),
            'occurred_at' => $this->occurredAt->canonical(),
            'category' => $this->category->value(),
            'severity' => $this->severity->value(),
            'disposition' => $this->disposition->value(),
            'origin' => $this->origin->value(),
            'throwable' => [
                'type' => $this->throwable::class,
                'message' => $this->throwable->getMessage(),
                'code' => $this->throwable->getCode(),
            ],
            'metadata' => $this->metadata,
        ];
    }

    private static function assertStructuredValue(mixed $value): void
    {
        if ($value === null || is_bool($value) || is_int($value) || is_float($value) || is_string($value)) {
            return;
        }
        if (!is_array($value)) {
            throw new InvalidFailureEnvelopeException('Failure metadata must contain structured scalar values only.');
        }
        foreach ($value as $nested) {
            self::assertStructuredValue($nested);
        }
    }
}
