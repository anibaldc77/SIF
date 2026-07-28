<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Contracts;

use Sif\Foundation\Logging\LogRecord;
use Sif\Foundation\Logging\Rendering\RenderedLogMessage;

interface LogMessageRendererInterface
{
    public function render(LogRecord $record): RenderedLogMessage;
}
