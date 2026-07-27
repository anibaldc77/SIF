<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

/** Produces a deterministic, safe array representation of an execution context. */
interface ContextSerializerInterface
{
    /** @return array<string, mixed> */
    public function serialize(
        ExecutionContextInterface $context,
        ?ContextRedactionPolicyInterface $redactionPolicy = null,
    ): array;
}
