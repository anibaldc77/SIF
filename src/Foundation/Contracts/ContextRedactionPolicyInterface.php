<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

/** Defines explicit attribute-key redaction for serialized execution contexts. */
interface ContextRedactionPolicyInterface
{
    public function redacts(string $attributeKey): bool;

    public function marker(): string;
}
