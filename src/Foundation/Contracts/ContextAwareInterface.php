<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

/** Exposes an execution context through an explicit integration boundary. */
interface ContextAwareInterface
{
    public function context(): ExecutionContextInterface;
}
