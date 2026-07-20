<?php

declare(strict_types=1);

namespace Sif\Builder\Reference\Parser;

final class ReferenceIdentifierNormalizer
{
    public function normalize(string $identifier): string
    {
        return strtoupper(trim($identifier));
    }

    public function isValid(string $identifier): bool
    {
        return preg_match('/^[A-Z][A-Z0-9]*(?:-[A-Z0-9]+)+$/', $identifier) === 1;
    }
}
