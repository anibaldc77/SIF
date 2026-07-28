<?php

declare(strict_types=1);

namespace Sif\Foundation\ErrorHandling\Contracts;

use Sif\Foundation\ErrorHandling\FailureOrigin;
use Sif\Foundation\ErrorHandling\Orchestration\ErrorHandlingResult;
use Throwable;

interface ErrorHandlerInterface
{
    /** @param array<string, mixed> $metadata */
    public function handle(Throwable $throwable, FailureOrigin $origin, array $metadata = [], int $attempt = 1): ErrorHandlingResult;
}
