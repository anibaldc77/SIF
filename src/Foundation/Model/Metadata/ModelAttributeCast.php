<?php

declare(strict_types=1);

namespace Sif\Foundation\Model\Metadata;

enum ModelAttributeCast: string
{
    case Mixed = 'mixed';
    case String = 'string';
    case Integer = 'integer';
    case Float = 'float';
    case Boolean = 'boolean';
    case Array = 'array';
    case Json = 'json';
    case DateTime = 'datetime';
    case ImmutableDateTime = 'immutable_datetime';
}
