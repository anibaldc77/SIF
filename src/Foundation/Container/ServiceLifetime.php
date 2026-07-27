<?php

declare(strict_types=1);

namespace Sif\Foundation\Container;

enum ServiceLifetime: string
{
    case Transient = 'transient';
    case Singleton = 'singleton';
    case Scoped = 'scoped';
}
