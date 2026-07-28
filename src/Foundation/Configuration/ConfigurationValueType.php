<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration;

use Sif\Foundation\Configuration\Exceptions\UnsupportedConfigurationValueException;

enum ConfigurationValueType: string
{
    case Null = 'null';
    case Boolean = 'boolean';
    case Integer = 'integer';
    case Float = 'float';
    case String = 'string';
    case Array = 'array';

    public static function fromValue(mixed $value): self
    {
        return match (true) {
            $value === null => self::Null,
            is_bool($value) => self::Boolean,
            is_int($value) => self::Integer,
            is_float($value) => self::Float,
            is_string($value) => self::String,
            is_array($value) => self::Array,
            default => throw UnsupportedConfigurationValueException::forValue($value),
        };
    }
}
