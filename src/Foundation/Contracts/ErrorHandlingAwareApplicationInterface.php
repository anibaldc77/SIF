<?php

declare(strict_types=1);

namespace Sif\Foundation\Contracts;

use Sif\Foundation\ErrorHandling\Contracts\ErrorHandlerInterface;
use Sif\Foundation\ErrorHandling\Orchestration\ErrorHandlingResult;

interface ErrorHandlingAwareApplicationInterface extends ApplicationInterface
{
    public function errorHandler(): ?ErrorHandlerInterface;

    public function lastErrorHandlingResult(): ?ErrorHandlingResult;
}
