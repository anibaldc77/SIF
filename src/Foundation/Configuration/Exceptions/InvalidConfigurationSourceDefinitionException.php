<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Exceptions;

final class InvalidConfigurationSourceDefinitionException extends ConfigurationSourceException
{
    public static function emptyIdentifier(): self
    {
        return new self('Configuration source identifier must not be empty.');
    }

    public static function emptyType(): self
    {
        return new self('Configuration source type must not be empty.');
    }

    public static function emptyDiagnosticField(): self
    {
        return new self('Configuration diagnostic code, message, and source identifier must not be empty.');
    }
}
