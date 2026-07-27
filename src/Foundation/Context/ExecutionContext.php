<?php

declare(strict_types=1);

namespace Sif\Foundation\Context;

use DateTimeImmutable;
use Sif\Foundation\Contracts\ExecutionContextInterface;

/**
 * Immutable execution identity and contextual metadata carrier.
 */
final readonly class ExecutionContext implements ExecutionContextInterface
{
    public function __construct(
        private ContextId $contextId,
        private ContextId $correlationId,
        private DateTimeImmutable $createdAt,
        private ContextAttributes $attributes = new ContextAttributes(),
        private ?ContextId $causationId = null,
        private ?ContextId $parentContextId = null,
        private ?string $actorId = null,
        private ?string $tenantId = null,
        private ?string $operation = null,
        private ?string $source = null,
        private ?string $locale = null,
        private ?string $timezone = null,
    ) {
    }

    public function contextId(): ContextId
    {
        return $this->contextId;
    }

    public function correlationId(): ContextId
    {
        return $this->correlationId;
    }

    public function causationId(): ?ContextId
    {
        return $this->causationId;
    }

    public function parentContextId(): ?ContextId
    {
        return $this->parentContextId;
    }

    public function actorId(): ?string
    {
        return $this->actorId;
    }

    public function tenantId(): ?string
    {
        return $this->tenantId;
    }

    public function operation(): ?string
    {
        return $this->operation;
    }

    public function source(): ?string
    {
        return $this->source;
    }

    public function locale(): ?string
    {
        return $this->locale;
    }

    public function timezone(): ?string
    {
        return $this->timezone;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function attributes(): ContextAttributes
    {
        return $this->attributes;
    }
}
