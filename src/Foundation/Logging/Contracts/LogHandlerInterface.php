<?php

declare(strict_types=1);

namespace Sif\Foundation\Logging\Contracts;

use Sif\Foundation\Logging\LogRecord;

interface LogHandlerInterface
{
    public function handle(LogRecord $record): void;
}
