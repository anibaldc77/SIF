<?php

declare(strict_types=1);

namespace Sif\Foundation\Exceptions;

final class DuplicateServiceProviderException extends FoundationException
{
    public static function forClass(string $providerClass): self
    {
        return new self(sprintf('Service provider "%s" is already registered.', $providerClass));
    }
}
