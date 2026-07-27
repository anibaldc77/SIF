<?php

declare(strict_types=1);

namespace Sif\Foundation\Context;

use Sif\Foundation\Contracts\ClockInterface;
use Sif\Foundation\Contracts\ContextIdGeneratorInterface;
use Sif\Foundation\Contracts\ExecutionContextFactoryInterface;
use Sif\Foundation\Contracts\ExecutionContextInterface;

/** Default immutable execution-context factory. */
final readonly class ExecutionContextFactory implements ExecutionContextFactoryInterface
{
    public function __construct(
        private ContextIdGeneratorInterface $idGenerator,
        private ClockInterface $clock,
    ) {
    }

    public function createRoot(
        ContextAttributes $attributes = new ContextAttributes(),
        ?string $actorId = null,
        ?string $tenantId = null,
        ?string $operation = null,
        ?string $source = null,
        ?string $locale = null,
        ?string $timezone = null,
    ): ExecutionContext {
        $contextId = $this->idGenerator->generate();

        return new ExecutionContext(
            contextId: $contextId,
            correlationId: $contextId,
            createdAt: $this->clock->now(),
            attributes: $attributes,
            actorId: $actorId,
            tenantId: $tenantId,
            operation: $operation,
            source: $source,
            locale: $locale,
            timezone: $timezone,
        );
    }

    public function derive(
        ExecutionContextInterface $parent,
        ContextAttributes $attributes = new ContextAttributes(),
        ?ContextId $causationId = null,
        ?string $operation = null,
        ?string $source = null,
    ): ExecutionContext {
        return new ExecutionContext(
            contextId: $this->idGenerator->generate(),
            correlationId: $parent->correlationId(),
            createdAt: $this->clock->now(),
            attributes: $parent->attributes()->merged($attributes),
            causationId: $causationId,
            parentContextId: $parent->contextId(),
            actorId: $parent->actorId(),
            tenantId: $parent->tenantId(),
            operation: $operation ?? $parent->operation(),
            source: $source ?? $parent->source(),
            locale: $parent->locale(),
            timezone: $parent->timezone(),
        );
    }
}
