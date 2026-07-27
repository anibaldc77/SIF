<?php

declare(strict_types=1);

namespace Sif\Foundation\Container;

enum ServiceDefinitionKind: string
{
    case ClassType = 'class';
    case Factory = 'factory';
    case Instance = 'instance';
    case Alias = 'alias';
}
