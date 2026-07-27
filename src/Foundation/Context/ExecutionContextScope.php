<?php

declare(strict_types=1);

namespace Sif\Foundation\Context;

use Sif\Foundation\Contracts\ContextCarrierInterface;
use Sif\Foundation\Contracts\ExecutionContextFactoryInterface;
use Sif\Foundation\Contracts\ExecutionContextInterface;
use Sif\Foundation\Contracts\ExecutionContextScopeInterface;

/**
 * Explicit context scope with no ambient or process-global state.
 *
 * The supplied operation receives the exact context instance carried by this
 * scope. Exceptions are propagated without translation.
 */
final readonly class ExecutionContextScope implements ExecutionContextScopeInterface
{
    public function __construct(
        private ExecutionContextInterface $executionContext,
        private ExecutionContextFactoryInterface $factory,
    ) {
    }

    public static function fromCarrier(
        ContextCarrierInterface $carrier,
        ExecutionContextFactoryInterface $factory,
    ): self {
        return new self($carrier->context(), $factory);
    }

    public function context(): ExecutionContextInterface
    {
        return $this->executionContext;
    }

    public function carrier(): ContextCarrier
    {
        return new ContextCarrier($this->executionContext);
    }

    public function derive(
        ContextAttributes $attributes = new ContextAttributes(),
        ?ContextId $causationId = null,
        ?string $operation = null,
        ?string $source = null,
    ): self {
        return new self(
            $this->factory->derive(
                parent: $this->executionContext,
                attributes: $attributes,
                causationId: $causationId,
                operation: $operation,
                source: $source,
            ),
            $this->factory,
        );
    }

    /**
     * @template T
     * @param callable(ExecutionContextInterface): T $operation
     * @return T
     */
    public function run(callable $operation): mixed
    {
        return $operation($this->executionContext);
    }
}
