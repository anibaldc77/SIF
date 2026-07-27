<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

/** Explicitly carries an execution context across an application boundary. */
interface ContextCarrierInterface
{
    public function context(): ExecutionContextInterface;
}
