<?php

declare(strict_types=1);

namespace Sif\Foundation\Configuration\Diagnostics;

enum ConfigurationDiagnosticSeverity: string
{
    case Info = 'info';
    case Warning = 'warning';
    case Error = 'error';
}
