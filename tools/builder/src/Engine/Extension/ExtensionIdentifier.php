<?php

declare(strict_types=1);

namespace Sif\Builder\Engine\Extension;

use Sif\Builder\Engine\Exception\InvalidExtensionIdentifierException;

final class ExtensionIdentifier
{
    public static function normalize(string $identifier): string
    {
        $identifier = strtolower(trim($identifier));

        if (!preg_match('/^[a-z0-9]+(?:\.[a-z0-9]+)*$/', $identifier)) {
            throw new InvalidExtensionIdentifierException(sprintf(
                'Extension identifier "%s" must be lowercase dot-separated segments.',
                $identifier,
            ));
        }

        return $identifier;
    }
}
