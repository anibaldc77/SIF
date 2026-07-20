<?php

declare(strict_types=1);

namespace Sif\Builder\Engine;

enum ExecutionPolicy: string
{
    case LENIENT = 'lenient';
    case STRICT = 'strict';
}
