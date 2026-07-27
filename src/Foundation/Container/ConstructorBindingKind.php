<?php

declare(strict_types=1);

namespace Sif\Foundation\Container;

enum ConstructorBindingKind: string
{
    case Value = 'value';
    case Service = 'service';
}
