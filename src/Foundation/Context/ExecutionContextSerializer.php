<?php

declare(strict_types=1);

namespace Sif\Foundation\Context;

use Sif\Foundation\Contracts\ContextRedactionPolicyInterface;
use Sif\Foundation\Contracts\ContextSerializerInterface;
use Sif\Foundation\Contracts\ExecutionContextInterface;

/** Canonical deterministic serializer for execution contexts. */
final class ExecutionContextSerializer implements ContextSerializerInterface
{
    /** @return array<string, mixed> */
    public function serialize(
        ExecutionContextInterface $context,
        ?ContextRedactionPolicyInterface $redactionPolicy = null,
    ): array {
        $policy = $redactionPolicy ?? ContextRedactionPolicy::none();

        return [
            'context_id' => $context->contextId()->value(),
            'correlation_id' => $context->correlationId()->value(),
            'causation_id' => $context->causationId()?->value(),
            'parent_context_id' => $context->parentContextId()?->value(),
            'actor_id' => $context->actorId(),
            'tenant_id' => $context->tenantId(),
            'operation' => $context->operation(),
            'source' => $context->source(),
            'locale' => $context->locale(),
            'timezone' => $context->timezone(),
            'created_at' => $context->createdAt()->format('Y-m-d\\TH:i:s.uP'),
            'attributes' => $this->normalizeAttributes($context->attributes()->all(), $policy),
        ];
    }

    /**
     * @param array<string, mixed> $attributes
     * @return array<string, mixed>
     */
    private function normalizeAttributes(
        array $attributes,
        ContextRedactionPolicyInterface $policy,
    ): array {
        ksort($attributes, SORT_STRING);

        foreach ($attributes as $key => $value) {
            if ($policy->redacts($key)) {
                $attributes[$key] = $policy->marker();
                continue;
            }

            $attributes[$key] = $this->normalizeValue($value, $policy);
        }

        return $attributes;
    }

    private function normalizeValue(mixed $value, ContextRedactionPolicyInterface $policy): mixed
    {
        if (!is_array($value)) {
            return $value;
        }

        if (array_is_list($value)) {
            return array_map(
                fn (mixed $item): mixed => $this->normalizeValue($item, $policy),
                $value,
            );
        }

        /** @var array<string, mixed> $value */
        return $this->normalizeAttributes($value, $policy);
    }
}
