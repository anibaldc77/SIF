<?php

declare(strict_types=1);

namespace Sif\Foundation\Context;

use Sif\Foundation\Contracts\ContextRedactionPolicyInterface;
use Sif\Foundation\Contracts\ContextSerializerInterface;
use Sif\Foundation\Contracts\ExecutionContextInterface;

/** Immutable diagnostic-safe snapshot produced through the canonical serializer. */
final readonly class ContextDiagnosticSnapshot
{
    /** @var array<string, mixed> */
    private array $payload;

    public function __construct(
        ExecutionContextInterface $context,
        ContextSerializerInterface $serializer,
        ?ContextRedactionPolicyInterface $redactionPolicy = null,
    ) {
        $this->payload = $serializer->serialize($context, $redactionPolicy);
    }

    public function contextId(): string
    {
        return (string) $this->payload['context_id'];
    }

    public function correlationId(): string
    {
        return (string) $this->payload['correlation_id'];
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return $this->payload;
    }
}
