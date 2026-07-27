<?php

declare(strict_types=1);

namespace Sif\Foundation\Exceptions;

use RuntimeException;
use Sif\Foundation\Container\ContainerValidationReport;
use Throwable;

final class ContainerCompilationException extends RuntimeException
{
    public function __construct(
        private readonly ContainerValidationReport $report,
        ?Throwable $cause = null,
    ) {
        parent::__construct(
            message: 'Container definition compilation failed.',
            previous: $cause,
        );
    }

    public function report(): ContainerValidationReport
    {
        return $this->report;
    }
}
