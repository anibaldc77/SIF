<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Processing;

use Sif\Foundation\Contracts\ExecutionContextInterface;
use Sif\Foundation\Logging\Context\ScopedLogAttributes;
use Sif\Foundation\Logging\Contracts\LogRecordProcessorInterface;
use Sif\Foundation\Logging\LogRecord;

/**
 * Projects the stable public execution-context contract into a scoped log attribute.
 */
final readonly class ExecutionContextEnricherProcessor implements LogRecordProcessorInterface
{
    public function __construct(
        private ExecutionContextInterface $context,
        private bool $overwrite = false,
        private bool $includeCustomAttributes = true,
    ) {
    }

    public function process(LogRecord $record): LogRecord
    {
        $context = array_filter([
            'context_id' => $this->context->contextId()->value(),
            'correlation_id' => $this->context->correlationId()->value(),
            'causation_id' => $this->context->causationId()?->value(),
            'parent_context_id' => $this->context->parentContextId()?->value(),
            'actor_id' => $this->context->actorId(),
            'tenant_id' => $this->context->tenantId(),
            'operation' => $this->context->operation(),
            'source' => $this->context->source(),
            'locale' => $this->context->locale(),
            'timezone' => $this->context->timezone(),
            'created_at' => $this->context->createdAt()->format('Y-m-d\\TH:i:s.uP'),
        ], static fn (mixed $value): bool => $value !== null);

        if ($this->includeCustomAttributes && !$this->context->attributes()->isEmpty()) {
            $context['attributes'] = $this->context->attributes()->all();
        }

        $scoped = (new ScopedLogAttributes('execution_context', $context))->nested();
        $attributes = $this->overwrite
            ? array_replace($record->attributes(), $scoped)
            : $record->attributes() + $scoped;

        return new LogRecord(
            $record->timestamp(),
            $record->level(),
            $record->channel(),
            $record->message(),
            $attributes,
            $record->throwable(),
            $record->recordId(),
        );
    }
}
