<?php

declare(strict_types=1);

namespace Sif\Foundation\Context;

use Sif\Foundation\Contracts\ContextCarrierInterface;
use Sif\Foundation\Contracts\ExecutionContextInterface;

/** Immutable explicit carrier for an execution context. */
final readonly class ContextCarrier implements ContextCarrierInterface
{
    public function __construct(private ExecutionContextInterface $executionContext)
    {
    }

    public function context(): ExecutionContextInterface
    {
        return $this->executionContext;
    }

    public function withContext(ExecutionContextInterface $context): self
    {
        if ($context === $this->executionContext) {
            return $this;
        }

        return new self($context);
    }
}
