<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

/** Explicitly associates an object payload with an execution context. */
interface ContextEnvelopeInterface extends ContextAwareInterface
{
    public function payload(): object;
}
