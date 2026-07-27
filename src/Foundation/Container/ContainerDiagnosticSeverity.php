<?php

declare(strict_types=1);

namespace Sif\Foundation\Container;

enum ContainerDiagnosticSeverity: string
{
    case Info = 'info';
    case Warning = 'warning';
    case Error = 'error';
}
