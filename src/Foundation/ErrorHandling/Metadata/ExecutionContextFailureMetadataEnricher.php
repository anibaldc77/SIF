<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Metadata;

use Sif\Foundation\Contracts\ExecutionContextInterface;
use Sif\Foundation\ErrorHandling\Contracts\FailureMetadataEnricherInterface;

final readonly class ExecutionContextFailureMetadataEnricher implements FailureMetadataEnricherInterface
{
    public function __construct(
        private ExecutionContextInterface $context,
        private bool $overwrite = false,
        private bool $includeCustomAttributes = false,
    ) {
    }

    public function enrich(array $metadata): array
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

        if ($this->overwrite || !isset($metadata['execution_context'])) {
            $metadata['execution_context'] = $context;
        }
        return $metadata;
    }
}
