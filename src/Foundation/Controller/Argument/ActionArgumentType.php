<?php

declare(strict_types=1);

namespace Sif\Foundation\Controller\Argument;

enum ActionArgumentType: string
{
    case String = 'string';
    case Integer = 'int';
    case Float = 'float';
    case Boolean = 'bool';
    case Array = 'array';
    case Mixed = 'mixed';
    case Request = 'request';
    case Context = 'context';
    case Service = 'service';
}
