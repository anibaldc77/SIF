<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\Context\ContextAttributes;
use Sif\Foundation\Context\ContextId;
use Sif\Foundation\Context\ExecutionContext;

/** Creates root contexts and derives immutable child contexts. */
interface ExecutionContextFactoryInterface
{
    public function createRoot(
        ContextAttributes $attributes = new ContextAttributes(),
        ?string $actorId = null,
        ?string $tenantId = null,
        ?string $operation = null,
        ?string $source = null,
        ?string $locale = null,
        ?string $timezone = null,
    ): ExecutionContext;

    public function derive(
        ExecutionContextInterface $parent,
        ContextAttributes $attributes = new ContextAttributes(),
        ?ContextId $causationId = null,
        ?string $operation = null,
        ?string $source = null,
    ): ExecutionContext;
}
