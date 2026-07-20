<?php

declare(strict_types=1);

namespace Sif\Builder\Engine\Diagnostic;

enum DiagnosticSeverity: int
{
    case INFO = 10;
    case WARNING = 20;
    case ERROR = 30;
    case FATAL = 40;

    public function label(): string
    {
        return strtolower($this->name);
    }
}
