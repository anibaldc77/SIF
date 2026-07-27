<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Context\ContextAttributes;
use Sif\Foundation\Context\ContextId;

/** Executes work with an explicitly supplied context and derives child scopes. */
interface ExecutionContextScopeInterface extends ContextCarrierInterface
{
    public function derive(
        ContextAttributes $attributes = new ContextAttributes(),
        ?ContextId $causationId = null,
        ?string $operation = null,
        ?string $source = null,
    ): self;

    /**
     * @template T
     * @param callable(ExecutionContextInterface): T $operation
     * @return T
     */
    public function run(callable $operation): mixed;
}
