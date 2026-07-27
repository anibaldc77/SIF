<?php

declare(strict_types=1);

namespace Sif\Foundation\Context;

use Sif\Foundation\Contracts\ContextEnvelopeInterface;
use Sif\Foundation\Contracts\ExecutionContextInterface;

/** Immutable explicit association between an object payload and a context. */
final readonly class ContextEnvelope implements ContextEnvelopeInterface
{
    public function __construct(
        private object $envelopedPayload,
        private ExecutionContextInterface $executionContext,
    ) {
    }

    public function payload(): object
    {
        return $this->envelopedPayload;
    }

    public function context(): ExecutionContextInterface
    {
        return $this->executionContext;
    }

    public function withPayload(object $payload): self
    {
        if ($payload === $this->envelopedPayload) {
            return $this;
        }

        return new self($payload, $this->executionContext);
    }

    public function withContext(ExecutionContextInterface $context): self
    {
        if ($context === $this->executionContext) {
            return $this;
        }

        return new self($this->envelopedPayload, $context);
    }
}
